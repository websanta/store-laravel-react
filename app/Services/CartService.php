<?php

namespace App\Services;

use App\Models\Product;

class CartService
{
    public function addItenToCart(Product $product, int $quantity = 1, array $optionIds = null)
    {
        //
    }

    public function updateItemQuantity(int $productId, int $quantity, $optionIds = null) {}

    public function removeItemFromCart(int $productId, $optionIds = null) {}

    public function getCartItems(): array
    {
        return [];
    }

    public function getTotalQuantity(): int
    {
        return 0;
    }

    public function getTotalPrice(): float
    {
        return 0;
    }

    protected function updateItemQuantityInDatabase(int $productId, int $quantity, array $optionIds)
    {
        //
    }

    protected function updateItemQuantityInCookies(int $productId, int $quantity, array $optionIds)
    {
        //
    }

    protected function saveItemToDatabase(int $productId, int $quantity, $price)
    {
        //
    }

    protected function saveItemToCookies(int $productId, int $quantity, $price)
    {
        //
    }

    protected function removeItemFromDatabase(int $productId, array $optionIds)
    {
        //
    }

    protected function removeItemFromCookies(int $productId, array $optionIds)
    {
        //
    }

    protected function getCartItemsFromDatabase(int $productId, array $optionIds)
    {
        //
    }

    protected function getCartItemsFromCookies(int $productId, array $optionIds)
    {
        //
    }
}
