<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('user', fn ($value) => User::active()->findOrFail($value));
        Route::bind('category', fn ($value) => Category::active()->findOrFail($value));
        Route::bind('product', fn ($value) => Product::active()->findOrFail($value));
        Route::bind('comment', fn ($value) => Comment::active()->findOrFail($value));
    }
}
