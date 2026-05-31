<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分のアカウントを削除できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/users/{$user->id}/delete");

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'         => $user->id,
            'is_invalid' => true,
        ]);
    }

    public function test_削除後はDBにレコードが残る(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson("/api/users/{$user->id}/delete");

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_削除後はログインできない(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson("/api/users/{$user->id}/delete");

        $response = $this->postJson('/api/users/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_削除後は会員情報を取得できない(): void
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();

        $this->actingAs($user)->postJson("/api/users/{$user->id}/delete");

        $response = $this->actingAs($admin)->getJson("/api/users/{$user->id}");

        $response->assertStatus(404);
    }

    public function test_未認証だと削除できない(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/delete");

        $response->assertStatus(401);
    }

    public function test_他の会員のアカウントは削除できない(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/users/{$other->id}/delete");

        $response->assertStatus(403);
    }

    public function test_管理者は任意のアカウントを削除できる(): void
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/users/{$user->id}/delete");

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'         => $user->id,
            'is_invalid' => true,
        ]);
    }
}
