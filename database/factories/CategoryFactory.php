<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => Str::title($name),
            'department_id' => Department::factory(),
            'parent_id' => null,
            'active' => true,
        ];
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Indicate that the category has a parent.
     */
    public function withParent(): static
    {
        return $this->state(fn(array $attributes) => [
            'parent_id' => CategoryFactory::new(),
        ]);
    }
}
