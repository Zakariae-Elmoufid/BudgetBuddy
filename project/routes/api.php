<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ExpenseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout',[AuthController::class],'logout')->middleware('auth:sanctum');;

Route::get('/test', function() {
    return response()->json(['message' => 'Hello, World!']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});


// Route::resource('/expenses', ExpenseController::class);
// Route::resource('expenses', ExpenseController::class)->only(['store', 'index']);

Route::post('/expenses', [ExpenseController::class, 'store'])->withoutMiddleware(['throttle']);
