<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerApproveTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者が申請中の会員を出品者承認できる(): void
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create(['seller_status' => 'pending']);

        $response = $this->actingAs($admin)->postJson("/api/users/{$user->id}/seller-approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'            => $user->id,
            'role'          => 'seller',
            'seller_status' => 'approved',
        ]);
    }

    public function test_未認証だと承認できない(): void
    {
        $user = User::factory()->create(['seller_status' => 'pending']);

        $response = $this->postJson("/api/users/{$user->id}/seller-approve");

        $response->assertStatus(401);
    }

    public function test_管理者以外は承認できない(): void
    {
        $member = User::factory()->create();
        $user   = User::factory()->create(['seller_status' => 'pending']);

        $response = $this->actingAs($member)->postJson("/api/users/{$user->id}/seller-approve");

        $response->assertStatus(403);
    }

    public function test_申請中でない会員は承認できない(): void
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create(['seller_status' => 'none']);

        $response = $this->actingAs($admin)->postJson("/api/users/{$user->id}/seller-approve");

        $response->assertStatus(409);
    }
}
