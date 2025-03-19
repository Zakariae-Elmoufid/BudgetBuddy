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

        $group = Expense::create([
            'group_id' => $id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'user_id' => $user->id,
        ]);


        $users = collect($request->users)->map(fn ($user_email) => ['email' => $user_email])->toArray();
        $userIds = User::whereIn('email', $request->users)->pluck('id');
        
        $group->users()->attach($userIds);
        
        return (new ExpenseGroupResource($group))->additional([
            'message' => 'expense Group added  successfully'
        ]); 
    }

    public function show($id){
        $group = Group::with(['expenses.users'])->find($id);
        return new ExpenseGroupResource($group);
    }
}
