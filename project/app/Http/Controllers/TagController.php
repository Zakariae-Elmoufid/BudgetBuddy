<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Resource\TagCollection;
use App\Resource\TagResource;

class TagController extends Controller
{    
    public function index(){
        $tags = Tag::all();
        return new TagCollection($tags);
    }

    public function store(Request $Request){
       $validated =  $Request->validate([
        'title' => 'required|string|max:60',
        ]);

        $tag = Tag::create([
            'title' => $validated['title'],
        ]);

        return (new ExpenseResource($tag))->additional([
            'message' => 'tag created successfully'
        ]);
    }

    public function show($id){
      $tas = Tag::findOrFail($id);
      return new TagResource($tag);
    }

    public function update(Request $request, $id){
        $tas = Tag::findOrFail($id);
        $validated =  $Request->validate([
            'title' => 'required|string|max:60',
            ]);
    
            $tag = Tag::create([
                'title' => $validated['title'],
            ]);

            return (new ExpenseResource($tag))->additional([
                'message' => 'tag updated successfully'
            ]);

    }

    public function destroy($id){
        $tas = Tag::findOrFail($id);
        $tag->delete();

        return response()->json([
            'message' => 'tag deleted successfully'
        ], 200);
    }


}
