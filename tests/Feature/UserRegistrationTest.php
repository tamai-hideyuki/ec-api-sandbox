<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_正常に会員登録できる(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'token',
                     'user' => ['id', 'name', 'email'],
                 ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_登録後にトークンが発行される(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_レスポンスにパスワードが含まれていない(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonMissingPath('user.password');
    }

    public function test_メールアドレスが重複していると登録できない(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/users', [
            'name'                  => '別のユーザー',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_メールアドレスの形式が不正だと登録できない(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'テストユーザー',
            'email'                 => 'not-an-email',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_名前が未入力だと登録できない(): void
    {
        $response = $this->postJson('/api/users', [
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_メールアドレスが未入力だと登録できない(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'テストユーザー',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_パスワードが未入力だと登録できない(): void
    {
        $response = $this->postJson('/api/users', [
            'name'  => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_短すぎるパスワードは登録できない(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_パスワード確認が一致しないと登録できない(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => 'テストユーザー',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_長すぎる名前は登録できない(): void
    {
        $response = $this->postJson('/api/users', [
            'name'                  => str_repeat('あ', 256),
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }
}
