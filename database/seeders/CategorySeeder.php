<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                "name" => "Electronics",
                "department_id" => 1,
                "parent_id" => null,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Fashion",
                "department_id" => 2,
                "parent_id" => null,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // Subcategories of Electronics (depth 1)
            [
                "name" => "Computers",
                "department_id" => 1,
                "parent_id" => 1,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // Subcategories of Electronics (depth 1)
            [
                "name" => "Smartphones",
                "department_id" => 1,
                "parent_id" => 1,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // Subcategories of Computers (depth 2)
            [
                "name" => "Laptops",
                "department_id" => 1,
                "parent_id" => 3,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // Subcategories of Computers (depth 2)
            [
                "name" => "Desktops",
                "department_id" => 1,
                "parent_id" => 3,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // Subcategories of Smartphones (depth 2)
            [
                "name" => "Android",
                "department_id" => 1,
                "parent_id" => 4,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],

            // Subcategories of Smartphones (depth 2)
            [
                "name" => "Apple",
                "department_id" => 1,
                "parent_id" => 4,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ];

        DB::table('categories')->insert($categories);
    }
}
