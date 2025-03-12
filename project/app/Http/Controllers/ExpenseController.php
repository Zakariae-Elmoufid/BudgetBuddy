<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\ExpenseCollection;
use App\Models\Tag;


class ExpenseController extends Controller

{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $user = auth()->user();
        $this->authorize('create', Expense::class);

        $expense = Expense::with('tags')->where('user_id', $user->id)->get();
        return  new ExpenseCollection($expense);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|min:20',
            'price' => 'required|numeric',
            'tags' => 'array',
            'tags.*' => 'string', 
        ]);
        
        $this->authorize('create', Expense::class);
        $expense = Expense::create([
            'title'  => $validated['title'],
            'description'  => $validated['description'],
            'price'  => $validated['price'],
            'user_id' => $user->id,
        ]);
        
        $tagData = collect($request->tags)->map(fn ($tag) => ['title' => $tag])->toArray();

        Tag::insert($tagData);
        
        $tagIds = Tag::whereIn('title', $request->tags)->pluck('id');
        
        $expense->tags()->attach($tagIds);

 
        return (new ExpenseResource($expense))->additional([
            'message' => 'Expense created successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->load('tags'); 
        $this->authorize('view', $expense);

        return new ExpenseResource($expense);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {       
        $user = auth()->user();
        $expense = Expense::findOrFail($id);
        $this->authorize('update', $expense);
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|min:20',
            'price' => 'required|numeric',
            'tags' => 'array',
            'tags.*' => 'string', 
        ]);
        
        $expense->load('tags'); 
        $expense->update([
            'title'  => $validated['title'],
            'description'  => $validated['description'],
            'price'  => $validated['price'],
            'user_id' => $user->id,
        ]);
        

        $expense->tags()->detach();
        if (isset($validated['tags'])) {
            $tagData = collect($validated['tags'])->map(fn ($tag) => ['title' => $tag])->toArray();
            Tag::insert($tagData);
    
            $tagIds = Tag::whereIn('title', $validated['tags'])->pluck('id');
    
            $expense->tags()->attach($tagIds);
        }


        return new ExpenseResource($expense->load('tags'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->load('tags'); 
        $expense->delete();

        return response()->json([
            'message' => 'expense deleted successfully'
        ], 200);
    }
}
