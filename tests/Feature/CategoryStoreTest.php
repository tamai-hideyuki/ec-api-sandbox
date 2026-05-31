<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者がカテゴリを登録できる(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson('/api/categories', [
            'name' => '家電',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'name'])
                 ->assertJsonFragment(['name' => '家電']);

        $this->assertDatabaseHas('categories', ['name' => '家電']);
    }

    public function test_未認証だと登録できない(): void
    {
        $response = $this->postJson('/api/categories', ['name' => '家電']);

        $response->assertStatus(401);
    }

    public function test_管理者以外は登録できない(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->postJson('/api/categories', [
            'name' => '家電',
        ]);

        $response->assertStatus(403);
    }

    public function test_名前が未入力だと登録できない(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson('/api/categories', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_名前が重複していると登録できない(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/categories', ['name' => '家電']);

        $response = $this->actingAs($admin)->postJson('/api/categories', ['name' => '家電']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_長すぎる名前は登録できない(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson('/api/categories', [
            'name' => str_repeat('あ', 256),
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }
}
