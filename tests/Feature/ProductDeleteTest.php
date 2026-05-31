<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_出品者が自分の商品を削除できる(): void
    {
        $seller  = User::factory()->seller()->create();
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($seller)->postJson("/api/products/{$product->id}/delete");

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_invalid' => true]);
    }

    public function test_削除後はDBにレコードが残る(): void
    {
        $seller  = User::factory()->seller()->create();
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($seller)->postJson("/api/products/{$product->id}/delete");

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_削除後は一覧に表示されない(): void
    {
        $seller  = User::factory()->seller()->create();
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($seller)->postJson("/api/products/{$product->id}/delete");

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)->assertJsonCount(0);
    }

    public function test_未認証だと削除できない(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson("/api/products/{$product->id}/delete");

        $response->assertStatus(401);
    }

    public function test_他の出品者の商品は削除できない(): void
    {
        $seller      = User::factory()->seller()->create();
        $otherSeller = User::factory()->seller()->create();
        $product     = Product::factory()->create(['user_id' => $otherSeller->id]);

        $response = $this->actingAs($seller)->postJson("/api/products/{$product->id}/delete");

        $response->assertStatus(403);
    }

    public function test_管理者は任意の商品を削除できる(): void
    {
        $admin   = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/products/{$product->id}/delete");

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_invalid' => true]);
    }
}
