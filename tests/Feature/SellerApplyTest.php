<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerApplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_一般会員が出品者申請できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/users/{$user->id}/seller-apply");

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'            => $user->id,
            'seller_status' => 'pending',
        ]);
    }

    public function test_未認証だと申請できない(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/seller-apply");

        $response->assertStatus(401);
    }

    public function test_他の会員の代わりに申請できない(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/users/{$other->id}/seller-apply");

        $response->assertStatus(403);
    }

    public function test_すでに申請中だと再申請できない(): void
    {
        $user = User::factory()->create(['seller_status' => 'pending']);

        $response = $this->actingAs($user)->postJson("/api/users/{$user->id}/seller-apply");

        $response->assertStatus(409);
    }

    public function test_すでに出品者承認済みだと申請できない(): void
    {
        $user = User::factory()->create(['seller_status' => 'approved', 'role' => 'seller']);

        $response = $this->actingAs($user)->postJson("/api/users/{$user->id}/seller-apply");

        $response->assertStatus(409);
    }
}
