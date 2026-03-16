<?php

namespace Database\Factories;

use App\Enums\ProductStatusEnum;
use App\Enums\VendorStatusEnum;
use App\Models\Category;
use App\Models\Department;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->words(3, true);

        $vendor = Vendor::factory()->create([
            'status' => VendorStatusEnum::Approved->value,
        ]);

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'department_id' => Department::factory(),
            'category_id' => Category::factory(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'status' => ProductStatusEnum::Published->value,
            'quantity' => fake()->numberBetween(1, 100),
            'created_by' => $vendor->user_id,
            'updated_by' => $vendor->user_id
        ];
    }
}
