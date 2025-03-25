<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use App\Models\Expense;
use App\Models\Payment;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupCollection;
use Illuminate\Validation\ValidationException ;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{

   
    
  public function index(){
    $user = auth()->user();
    
    $group = Group::whereHas('users', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->with('users')->get();
    return new GroupCollection($group);
  }

     
   public function store(GroupRequest $request) {
     $validated = $request->validated();
     
     $user = auth()->user();
          $group = Group::create([
              'name' => $validated['name'],
              'currency' => $validated['currency'],
              'solde' => $validated['solde'],
          ]);
  
          $group->users()->attach($user->id, ['role' => 'admin']);
  
          if (!empty($validated['users'])) {
              foreach ($validated['users'] as $user) {
                  $userObj = User::where('email', $user)->first();
                  if ($userObj) {
                      $group->users()->attach($userObj->id, ['role' => 'member']);
                  }
              }
          }

          return (new GroupResource($group))->additional([
            'message' => 'Group created successfully'
        ]);
  }

  public function show($id){
    $group = Group::findOrFail($id);
    $group->load('users'); 
     return new GroupResource($group);
  }

  public function delete($id){
    $group = Group::findOrFail($id);
    if($group->solde ==  0){
      $group->delete();
      return response()->json([
        'message' => 'Group deleted successfully'
    ], 200);
    }
    return response()->json([
      'message' => "don't delete group ,because hasn't soled"
  ], 200);
  }

  public function getBalances($id)
  {
      $group = Group::with(['expenses.users'])->findOrFail($id);
      
      $balances = [];
  
      foreach ($group->users as $user) {
          if (!isset($balances[$user->id])) {
              $balances[$user->id] = [
                  'name' => $user->name,
                  'total_paid' => 0,
                  'total_due' => 0,
                  'balance' => 0
              ];
          }
  
          $totalPaid = DB::table('expenses')
          ->join('expense_user' , 'expense_user.expense_id', '=' , 'expenses.id')
              ->where('expense_user.user_id', $user->id)
              ->where('expenses.group_id',$id)
              ->sum('user_amount');

          $totalDue = DB::table('expenses')
              ->join('expense_user', 'expenses.id', '=', 'expense_user.expense_id')
              ->where('expense_user.user_id', $user->id)
              ->selectRaw('SUM(expenses.amount_total / (SELECT COUNT(*) FROM expense_user WHERE expense_user.expense_id = expenses.id)) as total_due')
              ->groupBy('expenses.id')
              ->value('total_due');
           
        
          $balances[$user->id]['total_paid'] = $totalPaid;
          $balances[$user->id]['total_due'] = $totalDue ?? 0;
          $balances[$user->id]['balance'] = $totalPaid - ($totalDue ?? 0);
      }
      return new GroupResource($balances);

  }


  public function settlePayment(Request $request, $id)
{
    $request->validate([
        'payer_id' => 'required|exists:users,id',
        'receiver_id' => 'required|exists:users,id|different:payer_id',
        'amount' => 'required|numeric|min:0.01',
    ]);

    $group = Group::findOrFail($id);

    $payer = $group->users()->find($request->payer_id);
    $receiver = $group->users()->find($request->receiver_id);

    if (!$payer) {
        return response()->json(['error' => 'the payer soulde be to group'], 403);
    }

    if (!$receiver) {
        return response()->json(['error' => 'the receiver soulde be to group'], 403);
    }



    // save settle
    $payment = new Payment([
        'group_id' => $id,
        'payer_id' => $request->payer_id,
        'receiver_id' => $request->receiver_id,
        'amount' => $request->amount,
    ]);
    $payment->save();
    return new GroupResource($payment);
}

  
  
  
}
