<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分の注文一覧を取得できる(): void
    {
        $member = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $member->id]);
        Order::factory()->create();

        $response = $this->actingAs($member)->getJson('/api/orders');

        $response->assertStatus(200)->assertJsonCount(2);
    }

    public function test_管理者は全注文を取得できる(): void
    {
        $admin = User::factory()->admin()->create();
        Order::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/orders');

        $response->assertStatus(200)->assertJsonCount(3);
    }

    public function test_未認証だと取得できない(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }
}
