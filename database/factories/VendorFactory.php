<?php

namespace Database\Factories;

use App\Enums\VendorStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => VendorStatusEnum::Approved->value,
            'store_name' => fake()->company() . ' ' . fake()->unique()->numberBetween(1, 10000),
            'store_address' => fake()->address(),
            'cover_image' => null,
        ];
    }

    /**
     * Indicate that the vendor is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => VendorStatusEnum::Pending->value,
        ]);
    }

    /**
     * Indicate that the vendor is approved.
     */
    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => VendorStatusEnum::Approved->value,
        ]);
    }

    /**
     * Indicate that the vendor is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => VendorStatusEnum::Rejected->value,
        ]);
    }
}
