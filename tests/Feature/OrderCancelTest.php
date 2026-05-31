<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_注文者が自分の注文をキャンセルできる(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->create(['user_id' => $member->id]);

        $response = $this->actingAs($member)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_キャンセル後に在庫が戻る(): void
    {
        $member  = User::factory()->create();
        $order   = Order::factory()->create(['user_id' => $member->id, 'quantity' => 3]);
        $stock   = $order->product->stock;

        $this->actingAs($member)->postJson("/api/orders/{$order->id}/cancel");

        $this->assertDatabaseHas('products', ['id' => $order->product_id, 'stock' => $stock + 3]);
    }

    public function test_未認証だとキャンセルできない(): void
    {
        $order = Order::factory()->create();

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(401);
    }

    public function test_他人の注文はキャンセルできない(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->create();

        $response = $this->actingAs($member)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_管理者は任意の注文をキャンセルできる(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200);
    }

    public function test_キャンセル済みの注文は再キャンセルできない(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->cancelled()->create(['user_id' => $member->id]);

        $response = $this->actingAs($member)->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(409);
    }
}
