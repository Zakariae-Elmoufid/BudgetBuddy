<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\ExpenseCollection;
use App\Models\Tag;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     title="Expense Management API",
 *     version="1.0.0",
 *     description="API for managing user expenses with tags"
 * )
 */
class ExpenseController extends Controller

{
   

    /**
     * @OA\Get(
     *     path="/api/expenses",
     *     summary="get Collection of expenses",
     *     tags={"Expenses"},
     *     @OA\Response(
     *         response=200,
     *         description="Collection of expenses",
     *     ),
     *     @OA\Response(
     *     response=401,
     *      description="Unauthorized"
     *     ),
     *     @OA\Response(
     *     response=403,
     *     description="Forbidden"
     *     )
     * )
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

        /**
     * @OA\Post(
     *     path="/api/expenses",
     *     summary="Create a new expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "price"},
     *             @OA\Property(property="title", type="string", maxLength=100, example="Office Supplies"),
     *             @OA\Property(property="description", type="string", minLength=20, example="Purchased office supplies for the team meeting"),
     *             @OA\Property(property="price", type="number", format="float", example=150.75),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", example="office")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Expense created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Office Supplies"),
     *                 @OA\Property(property="description", type="string", example="Purchased office supplies for the team meeting"),
     *                 @OA\Property(property="price", type="number", format="float", example=150.75),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="office")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T12:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Expense created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"title": {"The title field is required."}}
     *             )
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $user = auth()->user();
       try {
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
        
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    }
        
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    

    /**
     * @OA\Get(
     *     path="/api/expenses/{id}",
     *     summary="Get a specific expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the expense",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Office Supplies"),
     *                 @OA\Property(property="description", type="string", example="Purchased office supplies for the team meeting"),
     *                 @OA\Property(property="price", type="number", format="float", example=150.75),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="office")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     )
     * )
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

       /**
     * @OA\Patch(
     *     path="/api/expenses/{id}",
     *     summary="Update an existing expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the expense",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "price"},
     *             @OA\Property(property="title", type="string", maxLength=100, example="Updated Office Supplies"),
     *             @OA\Property(property="description", type="string", minLength=20, example="Updated description for office supplies"),
     *             @OA\Property(property="price", type="number", format="float", example=200.50),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", example="updated")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Updated Office Supplies"),
     *                 @OA\Property(property="description", type="string", example="Updated description for office supplies"),
     *                 @OA\Property(property="price", type="number", format="float", example=200.50),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="updated")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"title": {"The title field is required."}}
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {       
        try {
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
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Delete(
     *     path="/api/expenses/{id}",
     *     summary="Delete an expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the expense",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="expense deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     )
     * )
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
