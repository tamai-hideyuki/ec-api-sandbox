<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_正常にログアウトできる(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/users/logout');

        $response->assertStatus(200);
    }

    public function test_ログアウト後にトークンが無効になる(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withToken($token)->postJson('/api/users/logout');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_未認証のままログアウトしようとすると401が返る(): void
    {
        $response = $this->postJson('/api/users/logout');

        $response->assertStatus(401);
    }
}
