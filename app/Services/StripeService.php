<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Events\OrderPaid;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeService
{
    public function handleCheckoutSessionCompleted(array $session): void
    {
        $orders = Order::query()
            ->with(['orderItems'])
            ->where('stripe_session_id', $session['id'])
            ->get();

        if ($orders->isEmpty()) {
            Log::warning('No orders found for session', ['session_id' => $session['id']]);
            return;
        }

        $productsToDeleteFromCart = [];

        foreach ($orders as $order) {
            try {
                $order->payment_intent = $session['payment_intent'];
                $order->status = OrderStatusEnum::Paid;
                $order->save();

                $productsToDeleteFromCart = array_merge(
                    $productsToDeleteFromCart,
                    $order->orderItems->pluck('product_id')->toArray()
                );

                $this->decrementProductQuantities($order);
            } catch (\Exception $e) {
                Log::error('Failed to save order', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        CartItem::query()
            ->where('user_id', $orders[0]->user_id)
            ->whereIn('product_id', $productsToDeleteFromCart)
            ->where('saved_for_later', false)
            ->delete();
    }

    public function handleChargeUpdated(array $charge, StripeClient $stripe): void
    {
        $transactionId  = $charge['balance_transaction'];
        $paymentIntent  = $charge['payment_intent'];

        $balanceTransaction = $stripe->balanceTransactions->retrieve($transactionId);

        $orders = Order::where('payment_intent', $paymentIntent)->get();

        if ($orders->isEmpty()) {
            Log::warning('No orders found for payment intent', ['payment_intent' => $paymentIntent]);
            return;
        }

        $totalAmount = $balanceTransaction['amount'];
        $stripeFee   = collect($balanceTransaction['fee_details'])
            ->where('type', 'stripe_fee')
            ->sum('amount');

        $platformFeePercent = config('app.platform_fee_pct');

        foreach ($orders as $order) {
            $vendorShare = $order->total_price / $totalAmount;

            $order->online_payment_comission = $vendorShare * $stripeFee;
            $order->website_comission        = ($order->total_price - $order->online_payment_comission) / 100 * $platformFeePercent;
            $order->vendor_subtotal          = $order->total_price - $order->website_comission - $order->online_payment_comission;
            $order->save();
        }

        event(new OrderPaid(collect($orders), [
            'payment_intent' => $paymentIntent,
            'transaction_id' => $transactionId,
        ]));
    }

    private function decrementProductQuantities(Order $order): void
    {
        try {
            foreach ($order->orderItems as $orderItem) {
                $options = $orderItem->variation_type_option_ids;
                $product = $orderItem->product;

                if ($options) {
                    $values    = array_values($options);
                    $variation = $product->variations->first(function ($v) use ($values) {
                        $dbOptions = is_string($v->variation_type_option_ids)
                            ? json_decode($v->variation_type_option_ids, true)
                            : $v->variation_type_option_ids;
                        return $dbOptions == $values;
                    });

                    if ($variation && $variation->quantity !== null) {
                        $variation->quantity -= $orderItem->quantity;
                        $variation->save();
                    }
                } elseif ($product->quantity !== null) {
                    $product->quantity -= $orderItem->quantity;
                    $product->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update quantities but order saved', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
