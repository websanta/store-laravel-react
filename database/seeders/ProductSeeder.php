<?php

namespace Database\Seeders;

use App\Enums\ProductStatusEnum;
use App\Enums\ProductVariationTypeEnum;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use App\Models\VariationTypeOption;
use App\Models\VariationTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendorUser = User::where('email', 'vendor@example.com')->first();
        $avendorUser = User::where('email', 'avendor@example.com')->first();

        if (!$vendorUser) {
            $this->command->error('Vendor not found!');
            return;
        }

        if (!$avendorUser) {
            $this->command->error('Apple Vendor not found!');
            return;
        }

        $this->createDemoProductsWithVariations($vendorUser);
        $this->createDemoProducts($vendorUser, $avendorUser);
    }

    private function createDemoProductsWithVariations($vendorUser)
    {
        // Creating product
        $department = Department::first();
        $category = $department->categories()->first();
        $product = Product::create([
            'title' => 'Huawei P30 Pro',
            'slug' => 'huawei-p30-pro',
            'description' => 'High-end smartphone with stunning display, professional camera system, and all-day battery life. Perfect for power users and content creators.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
            'price' => 350,
            'quantity' => 0,
            'status' => ProductStatusEnum::Published->value,
        ]);

        // Creating product variation types
        $colorType = VariationTypes::create([
            'product_id' => $product->id,
            'name' => 'Color',
            'type' => ProductVariationTypeEnum::Image->value
        ]);

        $storageType = VariationTypes::create([
            'product_id' => $product->id,
            'name' => 'Storage',
            'type' => ProductVariationTypeEnum::Radio->value
        ]);

        // Creating color options
        $orangeOption = VariationTypeOption::create([
            'variation_type_id' => $colorType->id,
            'name' => 'Orange',
        ]);

        $orangeImages = [
            'img/products/huawei-p30-pro/H-P30-orange-1.webp',
            'img/products/huawei-p30-pro/H-P30-orange-2.webp',
            'img/products/huawei-p30-pro/H-P30-orange-3.webp',
        ];

        foreach ($orangeImages as $imagePath) {
            $fullPath = public_path($imagePath);
            if (file_exists($fullPath)) {
                $orangeOption->addMedia($fullPath)
                    ->preservingOriginal()
                    ->toMediaCollection('images');
            }
        }

        $blueOption = VariationTypeOption::create([
            'variation_type_id' => $colorType->id,
            'name' => 'Blue',
        ]);

        $blueImages = [
            'img/products/huawei-p30-pro/H-P30-blue-1.webp',
            'img/products/huawei-p30-pro/H-P30-blue-2.webp',
            'img/products/huawei-p30-pro/H-P30-blue-3.webp',
        ];

        foreach ($blueImages as $imagePath) {
            $fullPath = public_path($imagePath);
            if (file_exists($fullPath)) {
                $blueOption->addMedia($fullPath)
                    ->preservingOriginal()
                    ->toMediaCollection('images');
            }
        }

        $lblueOption = VariationTypeOption::create([
            'variation_type_id' => $colorType->id,
            'name' => 'Light Blue',
        ]);

        $lblueImages = [
            'img/products/huawei-p30-pro/H-P30-lblue-1.webp',
            'img/products/huawei-p30-pro/H-P30-lblue-2.webp',
            'img/products/huawei-p30-pro/H-P30-lblue-3.webp',
        ];

        foreach ($lblueImages as $imagePath) {
            $fullPath = public_path($imagePath);
            if (file_exists($fullPath)) {
                $lblueOption->addMedia($fullPath)
                    ->preservingOriginal()
                    ->toMediaCollection('images');
            }
        }

        // Creating storage options
        $storage128Option = VariationTypeOption::create([
            'variation_type_id' => $storageType->id,
            'name' => '128 GB',
        ]);

        $storage256Option = VariationTypeOption::create([
            'variation_type_id' => $storageType->id,
            'name' => '256 GB',
        ]);

        $storage512Option = VariationTypeOption::create([
            'variation_type_id' => $storageType->id,
            'name' => '512 GB',
        ]);

        // We bind variation types to the product (update product_id)
        $colorType->update(['product_id' => $product->id]);
        $storageType->update(['product_id' => $product->id]);

        // Creating product variations
        $variations = [
            // Orange 128GB
            [
                'variation_type_option_ids' => [$orangeOption->id, $storage128Option->id],
                'quantity' => 5,
                'price' => 350,
            ],
            // Orange 256GB
            [
                'variation_type_option_ids' => [$orangeOption->id, $storage256Option->id],
                'quantity' => 35,
                'price' => 450,
            ],
            // Orange 512GB
            [
                'variation_type_option_ids' => [$orangeOption->id, $storage512Option->id],
                'quantity' => 20,
                'price' => 550,
            ],
            // Blue 128GB
            [
                'variation_type_option_ids' => [$blueOption->id, $storage128Option->id],
                'quantity' => 40,
                'price' => 370,
            ],
            // Blue 256GB
            [
                'variation_type_option_ids' => [$blueOption->id, $storage256Option->id],
                'quantity' => 25,
                'price' => 470,
            ],
            // Blue 512GB
            [
                'variation_type_option_ids' => [$blueOption->id, $storage512Option->id],
                'quantity' => 15,
                'price' => 570,
            ],
            // Light Blue 128GB
            [
                'variation_type_option_ids' => [$lblueOption->id, $storage128Option->id],
                'quantity' => 30,
                'price' => 380,
            ],
            // Light Blue 256GB
            [
                'variation_type_option_ids' => [$lblueOption->id, $storage256Option->id],
                'quantity' => 20,
                'price' => 480,
            ],
            // Light Blue 512GB
            [
                'variation_type_option_ids' => [$lblueOption->id, $storage512Option->id],
                'quantity' => 10,
                'price' => 580,
            ],
        ];

        foreach ($variations as $variationData) {
            ProductVariation::create([
                'product_id' => $product->id,
                'variation_type_option_ids' => $variationData['variation_type_option_ids'],
                'quantity' => $variationData['quantity'],
                'price' => $variationData['price'],
            ]);
        }
    }

    private function createDemoProducts($vendorUser, $avendorUser)
    {
        // iPhone 17 Pro Max
        $department = Department::where('slug', 'electronics')->firstOrFail();
        $category = $category = $department->categories()->where('name', 'Apple')->firstOrFail();
        $product = Product::create([
            'title' => 'iPhone 17 Pro Max',
            'slug' => 'iphone-17-pro-max',
            'description' => 'The iPhone 17 Pro Max pushes boundaries with its A19 Bionic chip, a pro-grade camera system for stunning photos and videos, and a super-bright ProMotion display — all in a durable, all-day design.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'created_by' => $avendorUser->id,
            'updated_by' => $avendorUser->id,
            'price' => 1300,
            'quantity' => 55,
            'status' => ProductStatusEnum::Published->value,
        ]);

        $productImages = [
            'img/products/iphone-17-pro-max/iPhone-17-Pro-Max-1.webp',
            'img/products/iphone-17-pro-max/iPhone-17-Pro-Max-2.webp',
            'img/products/iphone-17-pro-max/iPhone-17-Pro-Max-3.webp',
        ];

        foreach ($productImages as $imagePath) {
            $this->addImageIfExists($product, $imagePath, 'images');
        }

        // MacBook-Air-15-M4
        $department = Department::where('slug', 'electronics')->firstOrFail();
        $category = $category = $department->categories()->where('name', 'Laptops')->firstOrFail();
        $product = Product::create([
            'title' => 'MacBook Air 15 M4',
            'slug' => 'macbook-air-15-m4',
            'description' => 'Apple MacBook Air M4 15 16GB/512GB Sky Blue — 2025 model with Apple Intelligence, which simplifies system management, interaction with Siri, and processing of text and images.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'created_by' => $avendorUser->id,
            'updated_by' => $avendorUser->id,
            'price' => 1800,
            'quantity' => 22,
            'status' => ProductStatusEnum::Published->value,
        ]);

        $productImages = [
            'img/products/macbook-air-15-m4/MacBook-Air-15-M4-1.webp',
            'img/products/macbook-air-15-m4/MacBook-Air-15-M4-2.webp',
            'img/products/macbook-air-15-m4/MacBook-Air-15-M4-3.webp',
        ];

        foreach ($productImages as $imagePath) {
            $this->addImageIfExists($product, $imagePath, 'images');
        }

        // Toshiba-55C450ME
        $department = Department::where('slug', 'household-appliances')->firstOrFail();
        $category = $category = $department->categories()->where('name', 'Household appliances')->firstOrFail();
        $product = Product::create([
            'title' => 'Toshiba-55C450ME',
            'slug' => 'toshiba-55c450me',
            'description' => 'The Toshiba 55C450ME TV has a 55-inch diagonal screen with a 4K resolution (3840x2160 pixels) and supports Smart TV.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
            'price' => 500,
            'quantity' => 67,
            'status' => ProductStatusEnum::Published->value,
        ]);

        $productImages = [
            'img/products/toshiba-55c450me/Toshiba-55C450ME-1.webp',
            'img/products/toshiba-55c450me/Toshiba-55C450ME-2.webp',
            'img/products/toshiba-55c450me/Toshiba-55C450ME-3.webp',
        ];

        foreach ($productImages as $imagePath) {
            $this->addImageIfExists($product, $imagePath, 'images');
        }
    }

    private function addImageIfExists($model, $path, $collection = 'images')
    {
        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            $model->addMedia($fullPath)
                ->preservingOriginal()
                ->toMediaCollection($collection);
            return true;
        }

        $this->command->warn("Image not found: {$path}");
        return false;
    }
}
