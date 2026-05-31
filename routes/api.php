<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/users', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/logout', [UserController::class, 'logout']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::post('/users/{user}/delete', [UserController::class, 'destroy']);
    Route::post('/users/{user}/seller-apply', [UserController::class, 'sellerApply']);
    Route::post('/users/{user}/seller-approve', [UserController::class, 'sellerApprove']);
    Route::post('/users/{user}/seller-reject', [UserController::class, 'sellerReject']);
});
