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
use Illuminate\Support\Facades\Auth;

class ExpenseGroupController extends Controller
{
    public function store(ExpenseGroupRequest $request, Group $group)
    {
        // Check if the user belongs to the group
        if (!$group->users->contains(Auth::id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        $user = auth()->user();
        DB::beginTransaction();
    
        try {
            // Create the expense object
            $expense = Expense::create([
                'group_id' => $group->id,
                'title' => $request->title,
                'description' => $request->description,
                'amount_total' => $request->amount,
                'user_id' => $user->id,
                'date' => $request->date ?? now(),
                'split_type' => $request->split_type,
                'category' => $request->category,
            ]);
    
            // Validation variables
            $totalPercentage = 0;
            $totalContributions = 0;
    
            // Check if the split is "equal" or "custom"
            foreach ($request->shares as $share) {
                // If the split is equal
                $percentage = ($expense->split_type === 'equal')
                    ? (100 / count($request->shares))  // Equal distribution
                    : $share['percentage'];            // Custom distribution
    
                // Calculate the amount for this user
                $amount = ($percentage / 100) * $expense->amount_total;
    
                // Save the expense share
                ExpenseShare::create([
                    'expense_id' => $expense->id,
                    'user_id' => $share['user_id'],
                    'percentage' => $percentage,
                    'amount' => $amount,
                ]);
    
                // Total calculations for validation
                $totalPercentage += $percentage;
                $totalContributions += $amount;
            }
    
            // Validate that the total percentage is 100%
            if (abs($totalPercentage - 100) > 0.01) {
                throw new \Exception('Total percentage must be 100%');
            }
    
            // Validate that the total contributions match the total amount
            if (abs($totalContributions - $request->amount) > 0.01) {
                throw new \Exception('Total contributions do not match expense amount');
            }
    
            DB::commit();
            return response()->json([
                'message' => 'Expense added successfully',
                'expense' => $expense->load('contributions', 'shares')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    

    public function show(Group $group){
        if (!$group->users->contains(Auth::id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new ExpenseGroupResource($group->load('users', 'expenses'));
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
