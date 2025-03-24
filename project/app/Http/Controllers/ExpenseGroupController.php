<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExpenseGroupRequest;
use App\Models\Expense;
use App\Models\User;
use App\Models\Group;
use App\Http\Resources\ExpenseGroupResource;
use App\Http\Resources\ExpenseGroupCollection;
class ExpenseGroupController extends Controller
{
    public function store(ExpenseGroupRequest $request,$id){
        $validated = $request->validated();
        $user = auth()->user();

        $usersData = collect($request->users)->map(fn ($user) => [
            'email' => $user['email'], 
            'amount' => $user['amount'],
        ])->toArray();

        $userAmounts = collect($usersData)->pluck('amount')->toArray();
         
        $amount_total = 0;
        foreach ($userAmounts as $amount) {
            $amount_total += (float) $amount;
        }

        $expense = Expense::create([
            'group_id' => $id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'user_id' => $user->id,
            'amount_total' =>$amount_total,
        ]);
         $expense_id = $expense->id;
            
            $userIds = User::whereIn('email', collect($usersData)->pluck('email'))->pluck('id');
            $amount ;
            foreach ($userIds as $index => $userId) {
            $expense->users()->attach($userId, ['user_amount' => $usersData[$index]['amount']]);

            }
        return (new ExpenseGroupResource($expense))->additional([
            'message' => 'expense Group added  successfully'
        ]); 
    }

    public function show($id){
        $group = Group::with(['expenses.users'])->find($id);
        return new ExpenseGroupResource($group);
    }


    public function delete($group_id,$expense_id){
        $group = Group::findOrFail($group_id);
        $expense = Expense::where('id',$expense_id)->where('group_id',$group_id)->firstOrFail();
        $expense->delete();
        return response()->json([
            'message' => 'expense deleted successfully'
        ], 200);
    }


    
}
