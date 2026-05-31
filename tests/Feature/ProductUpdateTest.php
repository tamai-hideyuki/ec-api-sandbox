<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_出品者が自分の商品を編集できる(): void
    {
        $seller  = User::factory()->seller()->create();
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($seller)->putJson("/api/products/{$product->id}", [
            'name'  => '更新後の商品名',
            'price' => 2000,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => '更新後の商品名', 'price' => 2000]);
    }

    public function test_未認証だと編集できない(): void
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", ['name' => '変更']);

        $response->assertStatus(401);
    }

    public function test_他の出品者の商品は編集できない(): void
    {
        $seller      = User::factory()->seller()->create();
        $otherSeller = User::factory()->seller()->create();
        $product     = Product::factory()->create(['user_id' => $otherSeller->id]);

        $response = $this->actingAs($seller)->putJson("/api/products/{$product->id}", ['name' => '勝手に変更']);

        $response->assertStatus(403);
    }

    public function test_管理者は任意の商品を編集できる(): void
    {
        $admin   = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/products/{$product->id}", [
            'name' => '管理者が変更',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => '管理者が変更']);
    }

    public function test_価格が0以下だと編集できない(): void
    {
        $seller  = User::factory()->seller()->create();
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($seller)->putJson("/api/products/{$product->id}", ['price' => 0]);

        $response->assertStatus(422)->assertJsonValidationErrors(['price']);
    }

    public function test_存在しないカテゴリIDには変更できない(): void
    {
        $seller  = User::factory()->seller()->create();
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($seller)->putJson("/api/products/{$product->id}", ['category_id' => 9999]);

        $response->assertStatus(422)->assertJsonValidationErrors(['category_id']);
    }
}
