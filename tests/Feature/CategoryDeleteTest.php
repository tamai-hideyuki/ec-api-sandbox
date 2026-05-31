<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者がカテゴリを削除できる(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/categories/{$category->id}/delete");

        $response->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id'         => $category->id,
            'is_invalid' => true,
        ]);
    }

    public function test_削除後はDBにレコードが残る(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->postJson("/api/categories/{$category->id}/delete");

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_削除後はカテゴリ一覧に表示されない(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->postJson("/api/categories/{$category->id}/delete");

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(0);
    }

    public function test_未認証だと削除できない(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson("/api/categories/{$category->id}/delete");

        $response->assertStatus(401);
    }

    public function test_管理者以外は削除できない(): void
    {
        $member   = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($member)->postJson("/api/categories/{$category->id}/delete");

        $response->assertStatus(403);
    }

    public function test_存在しないカテゴリは削除できない(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson('/api/categories/9999/delete');

        $response->assertStatus(404);
    }
}
