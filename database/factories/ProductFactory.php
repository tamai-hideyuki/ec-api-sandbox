<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory()->seller(),
            'category_id' => Category::factory(),
            'name'        => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price'       => fake()->numberBetween(100, 100000),
            'stock'       => fake()->numberBetween(0, 100),
            'is_invalid'  => false,
        ];
    }
}
