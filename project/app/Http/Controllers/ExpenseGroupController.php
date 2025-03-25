<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExpenseGroupRequest;
use App\Models\Expense;
use App\Models\User;
use App\Models\Group;
use App\Models\Contribution;
use App\Models\ExpenseShare;
use App\Http\Resources\ExpenseGroupResource;
use App\Http\Resources\ExpenseGroupCollection;
use Illuminate\Support\Facades\DB;

class ExpenseGroupController extends Controller
{
    public function store(ExpenseGroupRequest $request, Group $group){
        
        // if (!$group->users->contains(Auth::id())) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }
        $user = auth()->user();
        DB::beginTransaction();
        
        try {
            $expense = new Expense([
                'group_id' => $group->id,
                'title' =>$request->title,
                'description' => $request->description,
                'amount_total' => $request->amount,
                'user_id' => $user->id ,
                'date' => $request->date ?? now(),
                'split_type' => $request->split_type ,
                'category' => $request->category,
            ]);
            $expense->save();
           
            // Calculate shares
            $groupUsers = $group->users;
            if ($expense->split_type === 'equal') {
                $shareAmount = $expense->amount_total / count($groupUsers);
                foreach ($groupUsers as $user) {
                    $share = new ExpenseShare([
                        'expense_id' => $expense->id,
                        'user_id' => $user->id,
                        'percentage' => 100 / count($groupUsers),
                        'amount' => $shareAmount,
                    ]);
                    $share->save();
                }
            } else {
                // Custom split
                $totalPercentage = 0;
                foreach ($request->shares as $share) {
                    $expenseShare = new ExpenseShare([
                        'expense_id' => $expense->id,
                        'user_id' => $share['user_id'],
                        'percentage' => $share['percentage'],
                        'amount' => ($share['percentage'] / 100) * $expense->amount_total,
                    ]);
                    $expenseShare->save();
                    $totalPercentage += $share['percentage'];
                }
                
                // Validate total percentage
                if (abs($totalPercentage - 100) > 0.01) {
                    throw new \Exception('Total percentage must be 100%');
                }
            }
            
            
            

                $totalContributions = 0;

                foreach ($request->shares as $share) {
                    $newContribution = new Contribution([
                        'expense_id' => $expense->id,
                        'user_id' => $share['user_id'],
                        'user_amount' => $share['amount'],
                    ]);
                    $totalContributions += $share['amount'];
                }

                // Validate total contributions
                if (abs($totalContributions - $request->amount) > 0.01) {
                    throw new \Exception('Total contributions do not match expense amount');
                }
           

            DB::commit();
            return response()->json(['expense' => $expense->load('contributions', 'shares')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
            
        // return (new ExpenseGroupResource($expense))->additional([
        //     'message' => 'expense Group added  successfully'
        // ]); 
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
