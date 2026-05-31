<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分の会員情報を更新できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/users/{$user->id}", [
            'name'  => '更新後の名前',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name'  => '更新後の名前',
                     'email' => 'updated@example.com',
                 ]);

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => '更新後の名前',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_名前だけ更新できる(): void
    {
        $user = User::factory()->create(['email' => 'original@example.com']);

        $response = $this->actingAs($user)->putJson("/api/users/{$user->id}", [
            'name' => '新しい名前',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => '新しい名前', 'email' => 'original@example.com']);
    }

    public function test_パスワードを更新できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson("/api/users/{$user->id}", [
            'password'              => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ])->assertStatus(200);

        $response = $this->postJson('/api/users/login', [
            'email'    => $user->email,
            'password' => 'newpassword1',
        ]);

        $response->assertStatus(200);
    }

    public function test_レスポンスにパスワードが含まれていない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/users/{$user->id}", [
            'name' => '更新後の名前',
        ]);

        $response->assertStatus(200)
                 ->assertJsonMissingPath('password');
    }

    public function test_未認証だと更新できない(): void
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => '更新後の名前',
        ]);

        $response->assertStatus(401);
    }

    public function test_他の会員の情報は更新できない(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/users/{$other->id}", [
            'name' => '勝手に変更',
        ]);

        $response->assertStatus(403);
    }

    public function test_管理者は任意の会員情報を更新できる(): void
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/users/{$user->id}", [
            'name' => '管理者が変更',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => '管理者が変更']);
    }

    public function test_メールアドレスが重複していると更新できない(): void
    {
        $user  = User::factory()->create(['email' => 'original@example.com']);
        $other = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->putJson("/api/users/{$user->id}", [
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_メールアドレスの形式が不正だと更新できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/users/{$user->id}", [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_長すぎる名前は更新できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/users/{$user->id}", [
            'name' => str_repeat('あ', 256),
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_パスワード確認が一致しないと更新できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/users/{$user->id}", [
            'password'              => 'newpassword1',
            'password_confirmation' => 'differentpass',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }
}
