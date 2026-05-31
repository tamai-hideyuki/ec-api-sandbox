<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerRejectTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者が申請中の会員を却下できる(): void
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create(['seller_status' => 'pending']);

        $response = $this->actingAs($admin)->postJson("/api/users/{$user->id}/seller-reject");

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'            => $user->id,
            'role'          => 'member',
            'seller_status' => 'rejected',
        ]);
    }

    public function test_未認証だと却下できない(): void
    {
        $user = User::factory()->create(['seller_status' => 'pending']);

        $response = $this->postJson("/api/users/{$user->id}/seller-reject");

        $response->assertStatus(401);
    }

    public function test_管理者以外は却下できない(): void
    {
        $member = User::factory()->create();
        $user   = User::factory()->create(['seller_status' => 'pending']);

        $response = $this->actingAs($member)->postJson("/api/users/{$user->id}/seller-reject");

        $response->assertStatus(403);
    }

    public function test_申請中でない会員は却下できない(): void
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create(['seller_status' => 'none']);

        $response = $this->actingAs($admin)->postJson("/api/users/{$user->id}/seller-reject");

        $response->assertStatus(409);
    }
}
