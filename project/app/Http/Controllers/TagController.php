<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;

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
      $tag = Tag::findOrFail($id);
      return new TagResource($tag);
    }

    public function update(Request $request, $id){
        $tag = Tag::findOrFail($id);
        $validated =  $request->validate([
            'title' => 'required|string|max:60',
            ]);
    
            $tag->update([
                'title' => $validated['title'],
            ]);

            return (new TagResource($tag))->additional([
                'message' => 'tag updated successfully'
            ]);

    }

    public function destroy($id){
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json([
            'message' => 'tag deleted successfully'
        ], 200);
    }


}
