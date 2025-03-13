<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;

/**
 * @OA\Info(
 *     title="Tag Management API",
 *     version="1.0.0",
 *     description="API for managing  tags"
 * )
 */

class TagController extends Controller
{    
        /**
     * @OA\Get(
     *     path="/api/tags",
     *     summary="get Collection of tags",
     *     tags={"Tags"},
     *     @OA\Response(
     *         response=200,
     *         description="Collection of tags",
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
    public function index(){
        $tags = Tag::all();
        return new TagCollection($tags);
    }

         /**
     * @OA\Post(
     *     path="/api/tags",
     *     summary="Create a new tag",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", maxLength=100, example="food")   
     *           
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
     *                 @OA\Property(property="title", type="string", example="Office")
     *              
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


    public function store(Request $Request){
        try {

       $validated =  $Request->validate([
        'title' => 'required|string|max:60',
        ]);

        $tag = Tag::create([
            'title' => $validated['title'],
        ]);

        return (new ExpenseResource($tag))->additional([
            'message' => 'tag created successfully'
        ]);
    
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    }
    }


    /**
     * @OA\Get(
     *     path="/api/tags/{id}",
     *     summary="Get a specific tag",
     *     tags={"Tags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the tag",
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
     *         description="Tag not found"
     *     )
     * )
     */ 

    public function show($id){
      $tag = Tag::findOrFail($id);
      return new TagResource($tag);
    }
    
    
       /**
     * @OA\Put(
     *     path="/api/tags/{id}",
     *     summary="Update an existing tag",
     *     tags={"Tags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the tag",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", maxLength=100, example="Updated Office Supplies"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Updated Office Supplies"),
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

    public function update(Request $request, $id){
        $tag = Tag::findOrFail($id);
        try{
        $validated =  $request->validate([
            'title' => 'required|string|max:60',
            ]);
    
            $tag->update([
                'title' => $validated['title'],
            ]);

            return (new TagResource($tag))->additional([
                'message' => 'tag updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

    }


        /**
     * @OA\Delete(
     *     path="/api/tags/{id}",
     *     summary="Delete an tags",
     *     tags={"Tags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the tags",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag deleted successfully",
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
     *         description="Tags not found"
     *     )
     * )
     */

    public function destroy($id){
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json([
            'message' => 'tag deleted successfully'
        ], 200);
    }


}
