<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::active()
            ->when(request('category_id'), fn ($q) => $q->where('category_id', request('category_id')))
            ->when(request('keyword'), fn ($q) => $q->where('name', 'like', '%' . request('keyword') . '%'))
            ->with(['user:id,name', 'category:id,name'])
            ->get(['id', 'user_id', 'category_id', 'name', 'price', 'stock']);

        return response()->json($products);
    }

    public function show(Product $product)
    {
        $product->load(['user:id,name', 'category:id,name']);

        return response()->json($product->only(['id', 'user_id', 'category_id', 'name', 'description', 'price', 'stock']));
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $product = Product::create([
            'user_id'     => $request->user()->id,
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
        ]);

        return response()->json($product->only(['id', 'user_id', 'category_id', 'name', 'description', 'price', 'stock']), 201);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $product->update($request->only(['category_id', 'name', 'description', 'price', 'stock']));

        return response()->json($product->only(['id', 'user_id', 'category_id', 'name', 'description', 'price', 'stock']));
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->update(['is_invalid' => true]);

        return response()->json(['message' => '商品を削除しました。']);
    }
}
