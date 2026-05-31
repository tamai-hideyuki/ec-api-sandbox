<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::active()->get(['id', 'name']));
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('create', Category::class);

        $category = Category::create(['name' => $request->name]);

        return response()->json(['id' => $category->id, 'name' => $category->name], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', Category::class);

        $category->update(['name' => $request->name]);

        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', Category::class);

        $category->update(['is_invalid' => true]);

        return response()->json(['message' => 'カテゴリを削除しました。']);
    }
}
