<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    // GET /reviews
    public function test_ゲストでも評価一覧を取得できる(): void
    {
        Review::factory()->count(3)->create();

        $response = $this->getJson('/api/reviews');

        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonStructure([['id', 'rating', 'user_id', 'product_id']]);
    }

    public function test_論理削除済みの評価は一覧に含まれない(): void
    {
        Review::factory()->count(2)->create();
        Review::factory()->create(['is_invalid' => true]);

        $response = $this->getJson('/api/reviews');

        $response->assertStatus(200)->assertJsonCount(2);
    }

    // POST /reviews
    public function test_ログイン済みユーザーが評価を投稿できる(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating'     => 5,
            'body'       => '素晴らしい商品です。',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['rating' => 5]);

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $member->id,
            'product_id' => $product->id,
            'rating'     => 5,
        ]);
    }

    public function test_未認証だと評価を投稿できない(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating'     => 3,
        ]);

        $response->assertStatus(401);
    }

    public function test_評価が1未満だと投稿できない(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating'     => 0,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['rating']);
    }

    public function test_評価が5を超えると投稿できない(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating'     => 6,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['rating']);
    }

    public function test_同じ商品には二重に評価できない(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($member)->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating'     => 4,
        ]);

        $response = $this->actingAs($member)->postJson('/api/reviews', [
            'product_id' => $product->id,
            'rating'     => 5,
        ]);

        $response->assertStatus(409);
    }

    public function test_存在しない商品には評価できない(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/reviews', [
            'product_id' => 9999,
            'rating'     => 3,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['product_id']);
    }

    // PUT /reviews/{id}
    public function test_自分の評価を編集できる(): void
    {
        $member = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $member->id, 'rating' => 3]);

        $response = $this->actingAs($member)->putJson("/api/reviews/{$review->id}", [
            'rating' => 5,
            'body'   => '再評価しました。',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['rating' => 5]);
    }

    public function test_未認証だと評価を編集できない(): void
    {
        $review = Review::factory()->create();

        $response = $this->putJson("/api/reviews/{$review->id}", ['rating' => 1]);

        $response->assertStatus(401);
    }

    public function test_他人の評価は編集できない(): void
    {
        $member = User::factory()->create();
        $review = Review::factory()->create();

        $response = $this->actingAs($member)->putJson("/api/reviews/{$review->id}", ['rating' => 1]);

        $response->assertStatus(403);
    }

    public function test_管理者は任意の評価を編集できる(): void
    {
        $admin  = User::factory()->admin()->create();
        $review = Review::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/reviews/{$review->id}", ['rating' => 1]);

        $response->assertStatus(200);
    }

    // POST /reviews/{id}/delete
    public function test_自分の評価を削除できる(): void
    {
        $member = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $member->id]);

        $response = $this->actingAs($member)->postJson("/api/reviews/{$review->id}/delete");

        $response->assertStatus(200);

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'is_invalid' => true]);
    }

    public function test_削除後は評価一覧に表示されない(): void
    {
        $member = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $member->id]);

        $this->actingAs($member)->postJson("/api/reviews/{$review->id}/delete");

        $this->getJson('/api/reviews')->assertStatus(200)->assertJsonCount(0);
    }

    public function test_未認証だと評価を削除できない(): void
    {
        $review = Review::factory()->create();

        $response = $this->postJson("/api/reviews/{$review->id}/delete");

        $response->assertStatus(401);
    }

    public function test_他人の評価は削除できない(): void
    {
        $member = User::factory()->create();
        $review = Review::factory()->create();

        $response = $this->actingAs($member)->postJson("/api/reviews/{$review->id}/delete");

        $response->assertStatus(403);
    }

    public function test_管理者は任意の評価を削除できる(): void
    {
        $admin  = User::factory()->admin()->create();
        $review = Review::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/reviews/{$review->id}/delete");

        $response->assertStatus(200);
    }
}
