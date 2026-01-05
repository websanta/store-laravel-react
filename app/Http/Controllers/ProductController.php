<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductListResource;
use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function home()
    {
        $products = Product::query()
            ->published()
            ->paginate(12);
        return Inertia::render('Home', [
            'products' => ProductListResource::collection($products)
        ]);
    }

    public function show(Product $product) {}
}
