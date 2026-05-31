<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ゲストでも商品詳細を取得できる(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'name', 'description', 'price', 'stock'])
                 ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_存在しない商品IDは404が返る(): void
    {
        $response = $this->getJson('/api/products/9999');

        $response->assertStatus(404);
    }

    public function test_論理削除済みの商品は404が返る(): void
    {
        $product = Product::factory()->create(['is_invalid' => true]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(404);
    }
}
