<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Models\Group;
use App\Models\User;
use App\Models\Expense;
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
      'message' => "don't delete group ,because has soled"
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
           
          // Mise à jour du tableau des soldes
          $balances[$user->id]['total_paid'] = $totalPaid;
          $balances[$user->id]['total_due'] = $totalDue ?? 0;
          $balances[$user->id]['balance'] = $totalPaid - ($totalDue ?? 0);
      }
  
      return response()->json($balances, 200);
  }
  
  
  
  
}
