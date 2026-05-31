<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者がカテゴリを編集できる(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => '旧名前']);

        $response = $this->actingAs($admin)->putJson("/api/categories/{$category->id}", [
            'name' => '新しい名前',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => '新しい名前']);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => '新しい名前']);
    }

    public function test_未認証だと編集できない(): void
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/categories/{$category->id}", ['name' => '変更']);

        $response->assertStatus(401);
    }

    public function test_管理者以外は編集できない(): void
    {
        $member   = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($member)->putJson("/api/categories/{$category->id}", [
            'name' => '変更',
        ]);

        $response->assertStatus(403);
    }

    public function test_名前が未入力だと編集できない(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/categories/{$category->id}", []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_他のカテゴリと名前が重複していると編集できない(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => '既存カテゴリ']);
        $category = Category::factory()->create(['name' => '編集対象']);

        $response = $this->actingAs($admin)->putJson("/api/categories/{$category->id}", [
            'name' => '既存カテゴリ',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_自分自身の名前には更新できる(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => '変更なし']);

        $response = $this->actingAs($admin)->putJson("/api/categories/{$category->id}", [
            'name' => '変更なし',
        ]);

        $response->assertStatus(200);
    }

    public function test_存在しないカテゴリは編集できない(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->putJson('/api/categories/9999', [
            'name' => '変更',
        ]);

        $response->assertStatus(404);
    }
}
