<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_正常にログインできる(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/users/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'token',
                     'user' => ['id', 'name', 'email'],
                 ]);
    }

    public function test_ログイン後にトークンが発行される(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/users/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_レスポンスにパスワードが含まれていない(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/users/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonMissingPath('user.password');
    }

    public function test_メールアドレスが間違っていると認証できない(): void
    {
        User::factory()->create(['email' => 'correct@example.com']);

        $response = $this->postJson('/api/users/login', [
            'email'    => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_パスワードが間違っていると認証できない(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/users/login', [
            'email'    => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_メールアドレスが未入力だとログインできない(): void
    {
        $response = $this->postJson('/api/users/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_パスワードが未入力だとログインできない(): void
    {
        $response = $this->postJson('/api/users/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_登録したパスワードでログインできる(): void
    {
        $this->postJson('/api/users', [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => 'mypassword1',
            'password_confirmation' => 'mypassword1',
        ]);

        $response = $this->postJson('/api/users/login', [
            'email'    => 'test@example.com',
            'password' => 'mypassword1',
        ]);

        $response->assertStatus(200);
    }
}
