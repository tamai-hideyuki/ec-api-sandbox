<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分の注文詳細を取得できる(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->create(['user_id' => $member->id]);

        $response = $this->actingAs($member)->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $order->id]);
    }

    public function test_未認証だと取得できない(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(401);
    }

    public function test_他人の注文詳細は取得できない(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->create();

        $response = $this->actingAs($member)->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_管理者は任意の注文詳細を取得できる(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        $response = $this->actingAs($admin)->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
    }

    public function test_存在しない注文は404が返る(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->getJson('/api/orders/9999');

        $response->assertStatus(404);
    }
}
