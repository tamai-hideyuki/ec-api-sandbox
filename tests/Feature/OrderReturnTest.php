<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_注文者が返品申請できる(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->create(['user_id' => $member->id]);

        $response = $this->actingAs($member)->postJson("/api/orders/{$order->id}/return");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'return_requested']);
    }

    public function test_未認証だと返品申請できない(): void
    {
        $order = Order::factory()->create();

        $response = $this->postJson("/api/orders/{$order->id}/return");

        $response->assertStatus(401);
    }

    public function test_他人の注文は返品申請できない(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->create();

        $response = $this->actingAs($member)->postJson("/api/orders/{$order->id}/return");

        $response->assertStatus(403);
    }

    public function test_キャンセル済みの注文は返品申請できない(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->cancelled()->create(['user_id' => $member->id]);

        $response = $this->actingAs($member)->postJson("/api/orders/{$order->id}/return");

        $response->assertStatus(409);
    }

    public function test_管理者が返品申請を承認できる(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->returnRequested()->create();

        $response = $this->actingAs($admin)->postJson("/api/orders/{$order->id}/return-approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'return_approved']);
    }

    public function test_管理者以外は返品申請を承認できない(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->returnRequested()->create();

        $response = $this->actingAs($member)->postJson("/api/orders/{$order->id}/return-approve");

        $response->assertStatus(403);
    }

    public function test_返品申請中でない注文は承認できない(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/orders/{$order->id}/return-approve");

        $response->assertStatus(409);
    }

    public function test_管理者が返品申請を却下できる(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->returnRequested()->create();

        $response = $this->actingAs($admin)->postJson("/api/orders/{$order->id}/return-reject");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'return_rejected']);
    }

    public function test_管理者以外は返品申請を却下できない(): void
    {
        $member = User::factory()->create();
        $order  = Order::factory()->returnRequested()->create();

        $response = $this->actingAs($member)->postJson("/api/orders/{$order->id}/return-reject");

        $response->assertStatus(403);
    }

    public function test_返品申請中でない注文は却下できない(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/orders/{$order->id}/return-reject");

        $response->assertStatus(409);
    }

    public function test_返品承認後に在庫が戻る(): void
    {
        $admin  = User::factory()->admin()->create();
        $order  = Order::factory()->returnRequested()->create(['quantity' => 2]);
        $stock  = $order->product->stock;

        $this->actingAs($admin)->postJson("/api/orders/{$order->id}/return-approve");

        $this->assertDatabaseHas('products', ['id' => $order->product_id, 'stock' => $stock + 2]);
    }
}
