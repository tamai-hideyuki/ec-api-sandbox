<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_一般会員が商品を購入できる(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);

        $response = $this->actingAs($member)->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['total_price' => 2000, 'quantity' => 2]);

        $this->assertDatabaseHas('orders', [
            'user_id'    => $member->id,
            'product_id' => $product->id,
            'quantity'   => 2,
            'status'     => 'pending',
        ]);
    }

    public function test_購入後に在庫が減る(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($member)->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
    }

    public function test_未認証だと購入できない(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_在庫が不足していると購入できない(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create(['stock' => 2]);

        $response = $this->actingAs($member)->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $response->assertStatus(409);
    }

    public function test_数量が0以下だと購入できない(): void
    {
        $member  = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity'   => 0,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['quantity']);
    }

    public function test_存在しない商品は購入できない(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/orders', [
            'product_id' => 9999,
            'quantity'   => 1,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['product_id']);
    }
}
