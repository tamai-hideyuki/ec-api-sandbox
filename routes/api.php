<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/users', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::get('/comments', [CommentController::class, 'index']);
Route::get('/comments/{comment}', [CommentController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/logout', [UserController::class, 'logout']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::post('/users/{user}/delete', [UserController::class, 'destroy']);
    Route::post('/users/{user}/seller-apply', [UserController::class, 'sellerApply']);
    Route::post('/users/{user}/seller-approve', [UserController::class, 'sellerApprove']);
    Route::post('/users/{user}/seller-reject', [UserController::class, 'sellerReject']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::post('/categories/{category}/delete', [CategoryController::class, 'destroy']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::post('/products/{product}/delete', [ProductController::class, 'destroy']);

    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::post('/comments/{comment}/delete', [CommentController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/return', [OrderController::class, 'return']);
    Route::post('/orders/{order}/return-approve', [OrderController::class, 'returnApprove']);
    Route::post('/orders/{order}/return-reject', [OrderController::class, 'returnReject']);
});
