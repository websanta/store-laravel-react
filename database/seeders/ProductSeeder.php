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
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

        // === Orange Option ===
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
            $this->addMediaToModel($orangeOption, $imagePath, 'images');
        }

        // === Blue Option ===
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
            $this->addMediaToModel($blueOption, $imagePath, 'images');
        }

        // === Light Blue Option ===
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
            $this->addMediaToModel($lblueOption, $imagePath, 'images');
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
            ['variation_type_option_ids' => [$orangeOption->id, $storage128Option->id], 'quantity' => 5, 'price' => 350],
            ['variation_type_option_ids' => [$orangeOption->id, $storage256Option->id], 'quantity' => 35, 'price' => 450],
            ['variation_type_option_ids' => [$orangeOption->id, $storage512Option->id], 'quantity' => 20, 'price' => 550],
            ['variation_type_option_ids' => [$blueOption->id, $storage128Option->id], 'quantity' => 40, 'price' => 370],
            ['variation_type_option_ids' => [$blueOption->id, $storage256Option->id], 'quantity' => 25, 'price' => 470],
            ['variation_type_option_ids' => [$blueOption->id, $storage512Option->id], 'quantity' => 15, 'price' => 570],
            ['variation_type_option_ids' => [$lblueOption->id, $storage128Option->id], 'quantity' => 30, 'price' => 380],
            ['variation_type_option_ids' => [$lblueOption->id, $storage256Option->id], 'quantity' => 20, 'price' => 480],
            ['variation_type_option_ids' => [$lblueOption->id, $storage512Option->id], 'quantity' => 10, 'price' => 580],
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
            $this->addMediaToModel($product, $imagePath, 'images');
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
            $this->addMediaToModel($product, $imagePath, 'images');
        }

        // TV Toshiba-55C450ME
        $department = Department::where('slug', 'household-appliances')->firstOrFail();
        $category = $category = $department->categories()->where('name', 'Household appliances')->firstOrFail();
        $product = Product::create([
            'title' => 'Toshiba 55C450ME',
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
            $this->addMediaToModel($product, $imagePath, 'images');
        }

        // Stove Gorenje-GEC5C40WC
        $department = Department::where('slug', 'household-appliances')->firstOrFail();
        $category = $category = $department->categories()->where('name', 'Household appliances')->firstOrFail();
        $product = Product::create([
            'title' => 'Gorenje GEC5C40WC',
            'slug' => 'gorenje-gec5c40wc',
            'description' => 'The Gorenje GEC5C40WC electric stove is available in white. The hob is made of glass ceramics and is equipped with four burners with a diameter of 14.5 cm to 18 cm. An extended heating zone is provided for the use of bulk cookware. Residual heat indicators make it possible to cook food while switched off. At the bottom of the case there is a drawer for storing utensils.
The 70 liter oven is equipped with a double-layer glass door. It is equipped with nine operating modes, including convection. The possibility of cleaning the internal surface with steam has been implemented. The temperature during cooking can reach 275°C.
The built-in digital display shows all the main parameters. Control is carried out using rotary switches and touch buttons. The stove is equipped with deep and flat baking sheets and a metal grid.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
            'price' => 570,
            'quantity' => 12,
            'status' => ProductStatusEnum::Published->value,
        ]);

        $productImages = [
            'img/products/gorenje-gec5c40wc/Gorenje-GEC5C40WC-1.webp',
            'img/products/gorenje-gec5c40wc/Gorenje-GEC5C40WC-2.webp',
            'img/products/gorenje-gec5c40wc/Gorenje-GEC5C40WC-3.webp',
        ];

        foreach ($productImages as $imagePath) {
            $this->addMediaToModel($product, $imagePath, 'images');
        }

        // Microwave MW-Toshiba-MW3
        $department = Department::where('slug', 'household-appliances')->firstOrFail();
        $category = $category = $department->categories()->where('name', 'Household appliances')->firstOrFail();
        $product = Product::create([
            'title' => 'MW Toshiba MW3',
            'slug' => 'mw-toshiba-mw3',
            'description' => 'The Toshiba MW3-EM21PE solo microwave oven, white, has 11 power levels, which allows you to choose the most optimal option. The Child Lock feature prevents you from changing settings.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
            'price' => 100,
            'quantity' => 34,
            'status' => ProductStatusEnum::Published->value,
        ]);

        $productImages = [
            'img/products/mw-toshiba-mw3/MW-Toshiba-MW3-1.webp',
            'img/products/mw-toshiba-mw3/MW-Toshiba-MW3-2.webp',
            'img/products/mw-toshiba-mw3/MW-Toshiba-MW3-3.webp',
        ];

        foreach ($productImages as $imagePath) {
            $this->addMediaToModel($product, $imagePath, 'images');
        }

        // Screwdriver Makita DDF453SYX5
        $department = Department::where('slug', 'tools-and-accessories')->firstOrFail();
        $category = $category = $department->categories()->where('name', 'Tools & Accessories')->firstOrFail();
        $product = Product::create([
            'title' => 'Makita DDF453SYX5',
            'slug' => 'makita-ddf453syx5',
            'description' => 'The Makita DDF453SYX5 cordless screwdriver is suitable for working with a variety of materials, including concrete, wood, metal, and hard polymers. It can be used to drive a screw or drill a hole in a workpiece. The reverse function allows you to easily remove a stuck drill bit or fastener.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
            'price' => 150,
            'quantity' => 55,
            'status' => ProductStatusEnum::Published->value,
        ]);

        $productImages = [
            'img/products/makita-ddf453syx5/Makita-DDF453SYX5-1.webp',
            'img/products/makita-ddf453syx5/Makita-DDF453SYX5-2.webp',
            'img/products/makita-ddf453syx5/Makita-DDF453SYX5-3.webp',
        ];

        foreach ($productImages as $imagePath) {
            $this->addMediaToModel($product, $imagePath, 'images');
        }

        // Angle Grinder DeWalt DWP849X
        $department = Department::where('slug', 'tools-and-accessories')->firstOrFail();
        $category = $category = $department->categories()->where('name', 'Tools & Accessories')->firstOrFail();
        $product = Product::create([
            'title' => 'DeWalt DWP849X',
            'slug' => 'dewalt-dwp849x',
            'description' => 'The DeWalt DWP849X angle grinder is a hand-held grinding tool housed in a black and yellow plastic housing. It features a 3-meter power cable with a rubber braid to protect it from breakage and an additional side stop.
The grinder is powered by a 1.25 kW motor with a soft-start system and variable speed control. It is compatible with a 180 mm diameter disc, to which the grinding surface is attached with Velcro.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
            'price' => 300,
            'quantity' => 41,
            'status' => ProductStatusEnum::Published->value,
        ]);

        $productImages = [
            'img/products/dewalt-dwp849x/DeWalt-DWP849X-1.webp',
            'img/products/dewalt-dwp849x/DeWalt-DWP849X-2.webp',
            'img/products/dewalt-dwp849x/DeWalt-DWP849X-3.webp',
        ];

        foreach ($productImages as $imagePath) {
            $this->addMediaToModel($product, $imagePath, 'images');
        }

        // lawn Mower Huter GLM-6.0 ST
        $department = Department::where('slug', 'tools-and-accessories')->firstOrFail();
        $category = $category = $department->categories()->where('name', 'Tools & Accessories')->firstOrFail();
        $product = Product::create([
            'title' => 'Huter GLM-6.0 ST',
            'slug' => 'huter-glm-6-0-st',
            'description' => 'The Huter GLM-6.0 ST gasoline lawn mower is designed for large areas. Its powerful 6-horsepower engine means you will spend significantly less time and effort cutting grass than with a handheld tool.',
            'department_id' => $department->id,
            'category_id' => $category->id,
            'created_by' => $vendorUser->id,
            'updated_by' => $vendorUser->id,
            'price' => 620,
            'quantity' => 8,
            'status' => ProductStatusEnum::Published->value,
        ]);

        $productImages = [
            'img/products/huter-glm-6-0-st/Huter-GLM-6.0-ST-1.webp',
            'img/products/huter-glm-6-0-st/Huter-GLM-6.0-ST-2.webp',
            'img/products/huter-glm-6-0-st/Huter-GLM-6.0-ST-3.webp',
        ];

        foreach ($productImages as $imagePath) {
            $this->addMediaToModel($product, $imagePath, 'images');
        }

        $products = Product::with('media')->get();
        foreach ($products as $product) {
            echo "Product: {$product->title}\n";
            echo "Media count: " . $product->getMedia('images')->count() . "\n";
            foreach ($product->getMedia('images') as $media) {
                echo "  - {$media->file_name} at {$media->getUrl()}\n";
                echo "    Exists: " . (file_exists($media->getPath()) ? 'YES' : 'NO') . "\n";
            }
            echo "\n";
        }
    }

    private function addMediaToModel($model, string $imagePath, string $collection = 'images'): bool
    {
        $fullPath = public_path($imagePath);

        if (!file_exists($fullPath)) {
            $this->command->warn("Image not found: {$imagePath}");
            return false;
        }

        try {
            $media = $model->addMedia($fullPath)
                ->preservingOriginal()
                ->toMediaCollection($collection, 'public');

            if (empty($media->uuid) || is_null($media->order_column)) {
                $media->fill([
                    'uuid' => $media->uuid ?? Str::uuid()->toString(),
                    'order_column' => $media->order_column ?? $this->getNextMediaOrder($model, $collection),
                ]);
                $media->saveQuietly();
            }

            return true;
        } catch (\Exception $e) {
            $this->command->error("Media error [{$imagePath}]: " . $e->getMessage());
            throw $e;
        }
    }

    private function getNextMediaOrder($model, string $collection): int
    {
        return Media::query()
            ->where('model_type', get_class($model))
            ->where('model_id', $model->getKey())
            ->where('collection_name', $collection)
            ->max('order_column') + 1;
    }
}
