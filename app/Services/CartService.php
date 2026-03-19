<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class CartService
{
    private ?array $cachedCartItems = null;
    protected const COOKIE_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60 * 24 * 365;

    public function addItemToCart(Product $product, int $quantity = 1, ?array $optionIds = null)
    {
        if (!$optionIds) {
            $optionIds = $product->getFirstOptionsMap();
        }

        $price = $product->getPriceForOptions($optionIds);

        if (Auth::check()) {
            $this->saveItemToDatabase($product->id, $quantity, $price, $optionIds);
        } else {
            $this->saveItemToCookies($product->id, $quantity, $price, $optionIds);
        }
    }

    public function updateItemQuantity(int $productId, int $quantity, $optionIds = null)
    {
        if (Auth::check()) {
            $this->updateItemQuantityInDatabase($productId, $quantity, $optionIds);
        } else {
            $this->updateItemQuantityInCookies($productId, $quantity, $optionIds);
        }
    }

    public function removeItemFromCart(int $productId, $optionIds = null)
    {
        if (Auth::check()) {
            $this->removeItemFromDatabase($productId, $optionIds);
        } else {
            $this->removeItemFromCookies($productId, $optionIds);
        }
    }

    public function getCartItems(): array
    {
        try {
            if ($this->cachedCartItems === null) {
                if (Auth::check()) {
                    $cartItems = $this->getCartItemsFromDatabase();
                } else {
                    $cartItems = $this->getCartItemsFromCookies();
                }

                $productIds = collect($cartItems)->map(fn($item) => $item['product_id']);
                $products = Product::whereIn('id', $productIds)
                    ->with('user.vendor')
                    ->forWebsite()
                    ->get()
                    ->keyBy('id');

                $cartItemData = [];
                foreach ($cartItems as $cartItem) {
                    $product = data_get($products, $cartItem['product_id']);
                    if (!$product) continue;

                    $optionInfo = [];
                    $options = VariationTypeOption::with('variationType')
                        ->whereIn('id', $cartItem['option_ids'])
                        ->get()
                        ->keyBy('id');

                    $imageUrl = null;

                    foreach ($cartItem['option_ids'] as $option_id) {
                        $option = data_get($options, $option_id);
                        if (!$imageUrl) {
                            $imageUrl = $option->getFirstMediaUrl('images', 'small');
                        }
                        $optionInfo[] = [
                            'id' => $option->id,
                            'name' => $option->name,
                            'type' => [
                                'id' => $option->variationType->id,
                                'name' => $option->variationType->name,
                            ]
                        ];
                    }

                    $cartItemData[] = [
                        'id' => $cartItem['id'],
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'price' => $cartItem['price'],
                        'quantity' => $cartItem['quantity'],
                        'option_ids' => $cartItem['option_ids'],
                        'image' => $imageUrl ?: $product->getFirstMediaUrl('images', 'small'),
                        'options' => $optionInfo,
                        'user' => [
                            'id' => $product->created_by,
                            'name' => $product->user->vendor->store_name
                        ]
                    ];
                }

                $this->cachedCartItems = $cartItemData;
            }

            return $this->cachedCartItems;
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return [];
    }

    public function getTotalQuantity(): int
    {
        $totalQuantity = 0;
        foreach ($this->getCartItems() as $item) {
            $totalQuantity += $item['quantity'];
        }

        return $totalQuantity;
    }

    public function getTotalPrice(): float
    {
        $total = 0;

        foreach ($this->getCartItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    protected function updateItemQuantityInDatabase(int $productId, int $quantity, array $optionIds)
    {
        $userId = Auth::id();

        $cartItem = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->whereOptionIds($optionIds)
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $quantity
            ]);
        }
    }

    protected function updateItemQuantityInCookies(int $productId, int $quantity, array $optionIds)
    {
        $cartItems = $this->getCartItemsFromCookies();

        ksort($optionIds);

        // Use a uniquie key based on product ID and option IDs
        $itemKey = $productId . '_' . json_encode($optionIds);

        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] = $quantity;
        }

        // Save updated cart items back to the cookies
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    protected function saveItemToDatabase(int $productId, int $quantity, $price, array $optionIds)
    {
        $userId = Auth::id();
        ksort($optionIds);

        $cartItem = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->whereOptionIds($optionIds)
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => DB::raw('quantity + ' . $quantity)
            ]);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'variation_type_option_ids' => $optionIds
            ]);
        }
    }

    protected function saveItemToCookies(int $productId, int $quantity, $price, array $optionIds)
    {
        $cartItems = $this->getCartItemsFromCookies();

        ksort($optionIds);

        // Use a uniquie key based on product ID and option IDs
        $itemKey = $productId . '_' . json_encode($optionIds);

        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] += $quantity;
            $cartItems[$itemKey]['price'] = $price;
        } else {
            $cartItems[$itemKey] = [
                'id' => Str::uuid(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'option_ids' => $optionIds
            ];
        }

        // Save updated cart items back to the cookies
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    protected function removeItemFromDatabase(int $productId, array $optionIds)
    {
        $userId = Auth::id();

        ksort($optionIds);

        CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->whereOptionIds($optionIds)
            ->delete();
    }

    protected function removeItemFromCookies(int $productId, array $optionIds)
    {
        $cartItems = $this->getCartItemsFromCookies();

        ksort($optionIds);

        // Definethe Cart key
        $cartKey = $productId . '_' . json_encode($optionIds);

        // Remove the item from the cart
        unset($cartItems[$cartKey]);

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    protected function getCartItemsFromDatabase()
    {
        $userId = Auth::id();

        $cartItems = CartItem::where('user_id', $userId)
            ->get()
            ->map(function ($cartItem) {
                return [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'option_ids' => $cartItem->variation_type_option_ids
                ];
            })
            ->toArray();

        return $cartItems;
    }

    protected function getCartItemsFromCookies()
    {
        // $cartItems = $_COOKIE['cartItems'] ?? '[]';
        $cartItems = Cookie::get(self::COOKIE_NAME, '[]');
        $decoded = json_decode($cartItems, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function getCartItemsGrouped()
    {
        $cartItems = $this->getCartItems();
        return collect($cartItems)->groupBy(fn($item) => $item['user']['id'])
            ->map(fn($items, $userId) => [
                'user' => $items->first()['user'],
                'items' => $items->toArray(),
                'totalQuantity' => $items->sum('quantity'),
                'totalPrice' => $items->sum(fn($item) => $item['price'] * $item['quantity']),
            ])
            ->toArray();
    }

    // Moving Cart Items from Cookies to DB on Auth
    public function moveCartItemsToDatabase($userId)
    {
        // Get cart items from cookies
        $cartItems = $this->getCartItemsFromCookies();

        // Check if cart items are empty - exit
        if (empty($cartItems)) {
            Cookie::queue(self::COOKIE_NAME, '', -1);
            return;
        }

        // Receive all the user's products
        $userCartItems = CartItem::where('user_id', $userId)->get();

        // Loop through cart items and insert them to DB
        foreach ($cartItems as $cartItem) {
            // Check if the cart item have valid structure
            if (!is_array($cartItem) || !isset($cartItem['product_id'], $cartItem['quantity'], $cartItem['price'], $cartItem['option_ids'])) {
                Log::warning('Invalid cart item structure in cookies', ['cartItem' => $cartItem]);
                continue;
            }

            // Check if the cart item already exists for the user
            $existingItem = $userCartItems->first(function ($item) use ($cartItem) {
                return $item->product_id == $cartItem['product_id']
                    && $item->variation_type_option_ids == $cartItem['option_ids'];
            });

            if ($existingItem) {
                // If the Item exists, update the quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $cartItem['quantity'],
                    'price' => $cartItem['price']   // Optional: Update price if needed
                ]);
            } else {
                // If the item doesn't exist, create a new record
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price'],
                    'variation_type_option_ids' => $cartItem['option_ids']
                ]);
            }
        }

        // Clear the cart items from cookies
        Cookie::queue(self::COOKIE_NAME, '', -1);
    }

    public function clearCart()
    {
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->delete();
        } else {
            Cookie::queue(self::COOKIE_NAME, '', -1);
        }

        $this->cachedCartItems = null;

        return $this;
    }
}
