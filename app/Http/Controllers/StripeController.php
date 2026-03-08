<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\OrderViewResource;
use App\Mail\CheckoutCompleted;
use App\Mail\NewOrderMail;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Stripe\StripeClient;

class StripeController extends Controller
{
    public function success(Request $request)
    {
        $user = auth()->user();
        $session_id = $request->get('session_id');
        $orders = Order::where('stripe_session_id', $session_id)->get();
        if ($orders->count() === 0) {
            abort(404);
        }

        foreach ($orders as $order) {
            if ($order->user_id !== $user->id) {
                abort(403);
            }
        }

        return Inertia::render('Stripe/Success', [
            'orders' => OrderViewResource::collection($orders)->collection->toArray()
        ]);
    }

    public function failure()
    {
        return Inertia::render('Stripe/Failure');
        // return redirect()->route('dashboard')->with('error', 'Stripe payment failed!');
    }

    public function webhook(Request $request)
    {
        $stripe = new StripeClient(config('app.stripe_secret_key'));
        $webhook_secret = config('app.stripe_webhook_secret');
        $payload = $request->getContent();
        $sig_header = request()->header('Stripe-Signature');
        $event = null;

        Log::info('WEBHOOK HIT!', [
            'headers' => $request->headers->all(),
            'content' => $request->getContent()
        ]);

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $webhook_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error($e);
            // Invalid payload
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error($e);
            return response('Invalid payload', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $pi = $session['payment_intent'];

                // Find orders by session ID and set payment intent
                $orders = Order::query()
                    ->with(['orderItems'])
                    ->where('stripe_session_id', $session['id'])
                    ->get();

                if ($orders->isEmpty()) {
                    Log::warning('No orders found for session', ['session_id' => $session['id']]);
                    break;
                }

                $productsToDeletedFromCart = [];
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
                            if ($variation && $variation->quantity !== null) {
                                $variation->quantity -= $orderItem->quantity;
                                $variation->save();
                            }
                        } else if ($product->quantity !== null) {
                            $product->quantity -= $orderItem->quantity;
                            $product->save();
                        }
                    }
                }

                // Delete cart items
                CartItem::query()
                    ->where('user_id', $orders[0]->user_id)
                    ->whereIn('product_id', $productsToDeletedFromCart)
                    ->where('saved_for_later', false)
                    ->delete();
                break;

            case 'charge.updated':
                $charge = $event->data->object;
                $transactionId = $charge['balance_transaction'];
                $paymentIntent = $charge['payment_intent'];
                $balanceTransaction = $stripe->balanceTransactions->retrieve($transactionId);

                $orders = Order::where('payment_intent', $paymentIntent)->get();

                Log::info('Charge updated', [
                    'payment_intent' => $paymentIntent,
                    'orders_count' => $orders->count(),
                    'order_ids' => $orders->pluck('id')->toArray()
                ]);

                $totalAmount = $balanceTransaction['amount'];
                $stripeFee = 0;
                foreach ($balanceTransaction['fee_details'] as $fee_detail) {
                    if ($fee_detail['type'] === 'stripe_fee') {
                        $stripeFee = $fee_detail['amount'];
                    }
                }

                $platformFeePercent = config('app.platform_fee_pct');

                if ($orders->isEmpty()) {
                    Log::warning('No orders found for payment intent', ['payment_intent' => $paymentIntent]);
                    break;
                }

                foreach ($orders as $order) {
                    $vendorShare = $order->total_price / $totalAmount;

                    $order->online_payment_comission = $vendorShare * $stripeFee;
                    $order->website_comission = ($order->total_price - $order->online_payment_comission) / 100 * $platformFeePercent;
                    $order->vendor_subtotal = $order->total_price - $order->website_comission - $order->online_payment_comission;

                    $order->save();

                    // Send Email to vendors
                    try {
                        Mail::to($order->vendorUser)->send(new NewOrderMail($order));
                        Log::info('Vendor email sent', ['order_id' => $order->id, 'vendor' => $order->vendorUser->email]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send vendor email', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                    }
                }

                // Send Email to buyer
                try {
                    Mail::to($orders[0]->user)->send(new CheckoutCompleted($orders));
                    Log::info('Customer email sent', ['user' => $orders[0]->user->email]);
                } catch (\Exception $e) {
                    Log::error('Failed to send customer email', ['error' => $e->getMessage()]);
                }
                break;

            default:
                // echo 'Received unknown event type: ' . $event->type;
                Log::info('Unhandled event type', ['type' => $event->type]);
        }

        return response('', 200);
    }
}
