<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Models\Group;
use App\Models\User;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupCollection;
use Illuminate\Validation\ValidationException ;

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
    
  
}
