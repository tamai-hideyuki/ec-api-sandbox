<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::active()
            ->with(['user:id,name', 'product:id,name'])
            ->get(['id', 'user_id', 'product_id', 'rating', 'body', 'created_at']);

        return response()->json($reviews);
    }

    public function store(StoreReviewRequest $request)
    {
        $this->authorize('create', Review::class);

        $exists = Review::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'この商品にはすでに評価済みです。'], 409);
        }

        $review = Review::create([
            'user_id'    => $request->user()->id,
            'product_id' => $request->product_id,
            'rating'     => $request->rating,
            'body'       => $request->body,
        ]);

        return response()->json($review->only(['id', 'user_id', 'product_id', 'rating', 'body', 'created_at']), 201);
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->update($request->only(['rating', 'body']));

        return response()->json($review->only(['id', 'user_id', 'product_id', 'rating', 'body', 'created_at']));
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $review->update(['is_invalid' => true]);

        return response()->json(['message' => '評価を削除しました。']);
    }
}
