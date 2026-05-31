<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_ゲストでも商品一覧を取得できる(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonStructure([['id', 'name', 'price', 'stock']]);
    }

    public function test_論理削除済みの商品は一覧に含まれない(): void
    {
        Product::factory()->count(2)->create();
        Product::factory()->create(['is_invalid' => true]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_カテゴリで絞り込みできる(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $category->id]);
        Product::factory()->create();

        $response = $this->getJson("/api/products?category_id={$category->id}");

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_キーワードで商品名を検索できる(): void
    {
        Product::factory()->create(['name' => 'テスト商品ABC']);
        Product::factory()->create(['name' => 'テスト商品DEF']);
        Product::factory()->create(['name' => '関係ない商品']);

        $keyword  = urlencode('テスト商品');
        $response = $this->getJson("/api/products?keyword={$keyword}");

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_商品が存在しない場合は空配列を返す(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJson([]);
    }
}
