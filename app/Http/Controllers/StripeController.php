<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeController extends Controller
{
    public function success()
    {
        return view('stripe.success');
    }

    public function failure()
    {
        return view('stripe.success');
    }

    public function webhook(Request $request)
    {
        $stripe = new StripeClient(config('app.stripe_secret_key'));
        $endpoint_secret = config('app.stripe_endpoint_secret');
        $payload = $request->getContent();
        $sig_header = request()->header('Stripe-Signature');
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error($e);
            // Invalid payload
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error($e);
            return response('Invalid payload', 400);
        }

        Log::info("====================================");
        Log::info("====================================");
        Log::info($event->type);
        Log::info($event);

        // Handle the event
        switch ($event->type) {
            case 'charge.updated':
                $charge = $event->data->object;
                $transactionId = $charge['balance_transaction'];
                $paymentIntent = $charge['payment_intent'];
                $balanceTransaction = $stripe->balanceTransactions->retrieve($transactionId);

                $orders = Order::where('payment_intent', $paymentIntent)->get();

                $totalAmount = $balanceTransaction['amount'];
                $stripeFee = 0;
                foreach ($balanceTransaction['fee_details'] as $fee_detail) {
                    if ($fee_detail['type'] === 'stripe_fee') {
                        $stripeFee = $fee_detail['amount'];
                    }
                }
                $platformFeePercent = config('app.platform_fee_pct');

                foreach ($orders as $order) {
                    $vendorShare = $order->total_price / $totalAmount;

                    $order->online_payment_comission = $vendorShare * $stripeFee;
                    $order->website_comission = ($order->total_price - $order->online_payment_comission) / 100 * $platformFeePercent;
                    $order->vendor_subtotal = $order->total_price - $order->website_comission - $order->online_payment_comission;

                    $order->save();
                }

                // Send Email to buyer

            case 'checkout.submission.completed':
                $session = $event->data->object;
                $pi = $session['payment_intent'];

                // Find orders by session ID and set payment intent
                $orders = Order::query()
                    ->with(['orderItems'])
                    ->where(['stripe_session_id', $session['id']])
                    ->get();

                $productsToDeletedFormCart = [];
                foreach ($orders as $order) {
                    $order->payment_intent = $pi;
                    $order->status = OrderStatusEnum::Paid;
                    $order->save();

                    $productsToDeletedFromCart = [
                        ...$productsToDeletedFromCart,
                        ...$order->orderItems->map(fn($item) => $item->product_id)->toArray()
                    ];

                    // Reduce product quantity
                    foreach ($order->orderItems as $orderItem) {
                        /** @var OrderItem $orderItem */
                        $options = $orderItem->variation_type_option_ids;
                        $product = $orderItem->product;
                        if ($options) {
                            sort($options);
                            $variation = $product->variations()
                                ->where('variation_type_option_ids', $options)
                                ->first();
                            if ($variation && $variation->quantity != null) {
                                $variation->quantity -= $orderItem->quantity;
                                $variation->save();
                            }
                        } else if ($product->quantity != null) {
                            $product->quantity -= $orderItem->quantity;
                            $product->save();
                        }
                    }
                }

                CartItem::query()
                    ->where('user_id', $order->user_id)
                    ->whereIn('product_id', $productsToDeletedFromCart)
                    ->where('saved_for_later', false)
                    ->delete();

            default:
                echo 'Received unknown event type: ' . $event->type;
        }

        return response('', 200);
    }
}
