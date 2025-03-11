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
        $expense = Expenses::all();
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
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|min:20',
            'price' => 'required|numeric',
            'tags' => 'array',
            'tags.*' => 'string', 
        ]);
        
        $expense = Expense::create([
            'title'  => $validated['title'],
            'description'  => $validated['description'],
            'price'  => $validated['price'],
            'user_id' => $request->user_id,
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
