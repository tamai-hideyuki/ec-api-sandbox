<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::active()
            ->with(['user:id,name', 'product:id,name'])
            ->get(['id', 'user_id', 'product_id', 'body', 'created_at']);

        return response()->json($comments);
    }

    public function show(Comment $comment)
    {
        $comment->load(['user:id,name', 'product:id,name']);

        return response()->json($comment->only(['id', 'user_id', 'product_id', 'body', 'created_at']));
    }

    public function store(StoreCommentRequest $request)
    {
        $this->authorize('create', Comment::class);

        $comment = Comment::create([
            'user_id'    => $request->user()->id,
            'product_id' => $request->product_id,
            'body'       => $request->body,
        ]);

        return response()->json($comment->only(['id', 'user_id', 'product_id', 'body', 'created_at']), 201);
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $comment->update(['body' => $request->body]);

        return response()->json($comment->only(['id', 'user_id', 'product_id', 'body', 'created_at']));
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->update(['is_invalid' => true]);

        return response()->json(['message' => 'コメントを削除しました。']);
    }
}
