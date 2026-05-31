<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者は会員一覧を取得できる(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'name', 'email', 'role']]);

        $this->assertCount(4, $response->json());
    }

    public function test_未認証だと会員一覧を取得できない(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }

    public function test_管理者以外は会員一覧を取得できない(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_論理削除済みの会員は一覧に含まれない(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(2)->create();
        User::factory()->create(['is_invalid' => true]);

        $response = $this->actingAs($admin)->getJson('/api/users');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    public function test_レスポンスにパスワードが含まれていない(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->getJson('/api/users');

        $response->assertStatus(200);
        collect($response->json())->each(function ($user) {
            $this->assertArrayNotHasKey('password', $user);
        });
    }
}
