<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_ゲストでもカテゴリ一覧を取得できる(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'name']])
                 ->assertJsonCount(3);
    }

    public function test_論理削除済みのカテゴリは一覧に含まれない(): void
    {
        Category::factory()->count(2)->create();
        Category::factory()->create(['is_invalid' => true]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_カテゴリが存在しない場合は空配列を返す(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJson([]);
    }
}
