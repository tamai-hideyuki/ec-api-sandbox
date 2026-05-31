<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'user_id'     => User::factory(),
            'product_id'  => $product->id,
            'quantity'    => fake()->numberBetween(1, 5),
            'total_price' => $product->price,
            'status'      => 'pending',
        ];
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }

    public function returnRequested(): static
    {
        return $this->state(['status' => 'return_requested']);
    }
}
