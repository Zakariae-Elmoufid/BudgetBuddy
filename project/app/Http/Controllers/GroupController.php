<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Models\Group;
use App\Models\User;

class GroupController extends Controller
{
     
 public function store(GroupRequest $request){

    GroupUser::create($request->validated());
    
    $group = Group::create([
      'name' => $request->name,
      'currency' => $request->currency,
  ]);


  


 }
}
