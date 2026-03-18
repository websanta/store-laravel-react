<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            "name" => "User",
            "email" => "user@example.com",
        ])->assignRole(RolesEnum::User->value);

        User::factory()->create([
            "name" => "Admin",
            "email" => "admin@example.com",
            "password" => Hash::make(env('APP_ADMIN_PASSWORD'))
        ])->assignRole(RolesEnum::Admin->value);

        $vendorUser = User::factory()->create([
            "name" => "Vendor",
            "email" => "vendor@example.com",
            "password" => Hash::make(env('APP_VENDOR_PASSWORD'))
        ]);
        $vendorUser->assignRole(RolesEnum::Vendor->value);
        Vendor::create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatusEnum::Approved,
            'store_name' => 'Vendor-Store'
        ]);

        $vendorUser = User::factory()->create([
            "name" => "Apple Vendor",
            "email" => "avendor@example.com",
            "password" => Hash::make(env('APP_VENDOR_PASSWORD'))
        ]);
        $vendorUser->assignRole(RolesEnum::Vendor->value);
        Vendor::create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatusEnum::Approved,
            'store_name' => 'Apple-Store'
        ]);
    }
}
