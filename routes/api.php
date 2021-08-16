<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::post('/updateUserDetails', [AuthController::class, 'updateUserDetails'])->middleware('auth:sanctum');
Route::post('/changePassword', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
//updateUserDetails
//
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetToken']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
/*
Route::post('/reset-password-token', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetToken']);
Route::post('/new-password', [AuthController::class, 'setNewAccountPassword']);
*/