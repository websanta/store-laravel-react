<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class CheckoutService
{
    public function checkout(Request $request, array $cartItems): array
    {
        $orders = [];
        $lineItems = [];

        foreach ($cartItems as $item) {
            $user = $item['user'];
            $itemsList = $item['items'];

            $order = Order::create([
                'stripe_session_id' => null,
                'user_id'           => $request->user()->id,
                'vendor_user_id'    => $user['id'],
                'total_price'       => $item['totalPrice'],
                'status'            => OrderStatusEnum::Draft->value,
            ]);
            $orders[] = $order;

            foreach ($itemsList as $cartItem) {
                OrderItem::create([
                    'order_id'                    => $order->id,
                    'product_id'                  => $cartItem['product_id'],
                    'quantity'                    => $cartItem['quantity'],
                    'price'                       => $cartItem['price'],
                    'variation_type_option_ids'   => $cartItem['option_ids'],
                ]);

                if (config('app.payment_driver') === 'stripe') {
                    $lineItems[] = $this->buildStripeLineItem($cartItem);
                }
            }
        }

        return [$orders, $lineItems];
    }

    private function buildStripeLineItem(array $cartItem): array
    {
        $description = collect($cartItem['options'])->map(
            fn($opt) => "{$opt['type']['name']}: {$opt['name']}"
        )->implode(', ');

        $lineItem = [
            'price_data' => [
                'currency'     => config('app.currency'),
                'product_data' => [
                    'name'   => $cartItem['title'],
                    'images' => [$cartItem['image']],
                ],
                'unit_amount'  => $cartItem['price'] * 100,
            ],
            'quantity' => $cartItem['quantity'],
        ];

        if ($description) {
            $lineItem['price_data']['product_data']['description'] = $description;
        }

        return $lineItem;
    }
}
