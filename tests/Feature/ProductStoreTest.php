<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStoreTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(int $categoryId): array
    {
        return [
            'category_id' => $categoryId,
            'name'        => 'テスト商品',
            'description' => '商品説明',
            'price'       => 1000,
            'stock'       => 10,
        ];
    }

    public function test_出品者が商品を登録できる(): void
    {
        $seller   = User::factory()->seller()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($seller)->postJson('/api/products', $this->validPayload($category->id));

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'name', 'price', 'stock'])
                 ->assertJsonFragment(['name' => 'テスト商品', 'price' => 1000]);

        $this->assertDatabaseHas('products', ['name' => 'テスト商品', 'user_id' => $seller->id]);
    }

    public function test_未認証だと登録できない(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/products', $this->validPayload($category->id));

        $response->assertStatus(401);
    }

    public function test_一般会員は商品を登録できない(): void
    {
        $member   = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/products', $this->validPayload($category->id));

        $response->assertStatus(403);
    }

    public function test_管理者は商品を登録できない(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/products', $this->validPayload($category->id));

        $response->assertStatus(403);
    }

    public function test_商品名が未入力だと登録できない(): void
    {
        $seller   = User::factory()->seller()->create();
        $category = Category::factory()->create();
        $payload  = $this->validPayload($category->id);
        unset($payload['name']);

        $response = $this->actingAs($seller)->postJson('/api/products', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_価格が未入力だと登録できない(): void
    {
        $seller   = User::factory()->seller()->create();
        $category = Category::factory()->create();
        $payload  = $this->validPayload($category->id);
        unset($payload['price']);

        $response = $this->actingAs($seller)->postJson('/api/products', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['price']);
    }

    public function test_価格が0以下だと登録できない(): void
    {
        $seller   = User::factory()->seller()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($seller)->postJson('/api/products', array_merge($this->validPayload($category->id), ['price' => 0]));

        $response->assertStatus(422)->assertJsonValidationErrors(['price']);
    }

    public function test_存在しないカテゴリIDでは登録できない(): void
    {
        $seller = User::factory()->seller()->create();

        $response = $this->actingAs($seller)->postJson('/api/products', $this->validPayload(9999));

        $response->assertStatus(422)->assertJsonValidationErrors(['category_id']);
    }
}
