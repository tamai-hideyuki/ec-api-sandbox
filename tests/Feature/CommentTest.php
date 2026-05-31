<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    // GET /comments
    public function test_ゲストでもコメント一覧を取得できる(): void
    {
        Comment::factory()->count(3)->create();

        $response = $this->getJson('/api/comments');

        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonStructure([['id', 'body', 'user_id', 'product_id']]);
    }

    public function test_論理削除済みのコメントは一覧に含まれない(): void
    {
        Comment::factory()->count(2)->create();
        Comment::factory()->create(['is_invalid' => true]);

        $response = $this->getJson('/api/comments');

        $response->assertStatus(200)->assertJsonCount(2);
    }

    // GET /comments/{id}
    public function test_ゲストでもコメント詳細を取得できる(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $comment->id]);
    }

    public function test_存在しないコメントは404が返る(): void
    {
        $response = $this->getJson('/api/comments/9999');

        $response->assertStatus(404);
    }

    public function test_論理削除済みのコメントは404が返る(): void
    {
        $comment = Comment::factory()->create(['is_invalid' => true]);

        $response = $this->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(404);
    }

    // POST /comments
    public function test_ログイン済みユーザーがコメントを投稿できる(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/comments', [
            'product_id' => $product->id,
            'body'       => 'これは良い商品です。',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['body' => 'これは良い商品です。']);

        $this->assertDatabaseHas('comments', [
            'user_id'    => $member->id,
            'product_id' => $product->id,
            'body'       => 'これは良い商品です。',
        ]);
    }

    public function test_未認証だとコメントを投稿できない(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/comments', [
            'product_id' => $product->id,
            'body'       => 'コメント',
        ]);

        $response->assertStatus(401);
    }

    public function test_本文が未入力だとコメントを投稿できない(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/comments', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['body']);
    }

    public function test_存在しない商品にはコメントできない(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/comments', [
            'product_id' => 9999,
            'body'       => 'コメント',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['product_id']);
    }

    // PUT /comments/{id}
    public function test_自分のコメントを編集できる(): void
    {
        $member  = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $member->id]);

        $response = $this->actingAs($member)->putJson("/api/comments/{$comment->id}", [
            'body' => '編集後の内容',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['body' => '編集後の内容']);
    }

    public function test_未認証だとコメントを編集できない(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->putJson("/api/comments/{$comment->id}", ['body' => '編集']);

        $response->assertStatus(401);
    }

    public function test_他人のコメントは編集できない(): void
    {
        $member  = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($member)->putJson("/api/comments/{$comment->id}", [
            'body' => '勝手に編集',
        ]);

        $response->assertStatus(403);
    }

    public function test_管理者は任意のコメントを編集できる(): void
    {
        $admin   = User::factory()->admin()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/comments/{$comment->id}", [
            'body' => '管理者が編集',
        ]);

        $response->assertStatus(200);
    }

    // POST /comments/{id}/delete
    public function test_自分のコメントを削除できる(): void
    {
        $member  = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $member->id]);

        $response = $this->actingAs($member)->postJson("/api/comments/{$comment->id}/delete");

        $response->assertStatus(200);

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'is_invalid' => true]);
    }

    public function test_削除後はコメント一覧に表示されない(): void
    {
        $member  = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $member->id]);

        $this->actingAs($member)->postJson("/api/comments/{$comment->id}/delete");

        $response = $this->getJson('/api/comments');

        $response->assertStatus(200)->assertJsonCount(0);
    }

    public function test_未認証だとコメントを削除できない(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->postJson("/api/comments/{$comment->id}/delete");

        $response->assertStatus(401);
    }

    public function test_他人のコメントは削除できない(): void
    {
        $member  = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($member)->postJson("/api/comments/{$comment->id}/delete");

        $response->assertStatus(403);
    }

    public function test_管理者は任意のコメントを削除できる(): void
    {
        $admin   = User::factory()->admin()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/comments/{$comment->id}/delete");

        $response->assertStatus(200);
    }
}
