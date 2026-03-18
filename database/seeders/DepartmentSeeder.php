<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                "name" => "Electronics",
                "slug" => "electronics",
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Household appliances",
                "slug" => "household-appliances",
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Tools & Accessories",
                "slug" => "tools-and-accessories",
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ];

        DB::table('departments')->insert($departments);
    }
}
