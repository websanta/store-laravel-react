<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'price' => fake()->randomFloat(2, 10, 500),
            'variation_type_option_ids' => null,
            'saved_for_later' => false,
        ];
    }

    /**
     * Indicate that the cart item is saved for later.
     */
    public function savedForLater(): static
    {
        return $this->state(fn (array $attributes) => [
            'saved_for_later' => true,
        ]);
    }

    /**
     * Indicate that the cart item has variation options.
     */
    public function withOptions(array $optionIds): static
    {
        return $this->state(fn (array $attributes) => [
            'variation_type_option_ids' => $optionIds,
        ]);
    }
}
