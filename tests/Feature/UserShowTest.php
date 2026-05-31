<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分の会員情報を取得できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'name', 'email'])
                 ->assertJsonFragment(['id' => $user->id, 'email' => $user->email]);
    }

    public function test_レスポンスにパスワードが含まれていない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonMissingPath('password');
    }

    public function test_未認証だと会員情報を取得できない(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(401);
    }

    public function test_他の会員の情報は取得できない(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$other->id}");

        $response->assertStatus(403);
    }

    public function test_管理者は任意の会員情報を取得できる(): void
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();

        $response = $this->actingAs($admin)->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $user->id]);
    }

    public function test_存在しない会員IDだと404が返る(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->getJson('/api/users/9999');

        $response->assertStatus(404);
    }
}
