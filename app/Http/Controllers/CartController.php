<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\CheckoutService;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CartService $cartService)
    {
        return Inertia::render('Cart/Index', [
            'cartItems' => $cartService->getCartItemsGrouped()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product, CartService $cartService)
    {
        $request->mergeIfMissing([
            'quantity' => 1
        ]);

        $data = $request->validate([
            'option_ids' => ['nullable', 'array'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartService->addItemToCart(
            $product,
            $data['quantity'],
            $data['option_ids'] ?: []
        );

        return back()->with('success', 'Product successfully added to cart');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, CartService $cartService)
    {
        $request->validate([
            'quantity' => ['integer', 'min:1'],
        ]);

        $optionIds = $request->input('option_ids') ?: [];

        $quantity = $request->input('quantity');

        $cartService->updateItemQuantity($product->id, $quantity, $optionIds);

        return back()->with('success', 'Quantity was successfully updated in cart');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product, CartService $cartService)
    {
        $optionIds = $request->input('option_ids');

        $cartService->removeItemFromCart($product->id, $optionIds);

        return back()->with('success', 'Product was successfully removed from cart');
    }

    public function checkout(Request $request, CartService $cartService, CheckoutService $checkoutService)
    {
        $vendorId    = $request->input('vendor_id');
        $allCartItems = $cartService->getCartItemsGrouped();

        DB::beginTransaction();

        try {
            $checkoutCartItems = $allCartItems;
            if ($vendorId) {
                if (!isset($allCartItems[$vendorId])) {
                    throw new \Exception('Vendor not found in cart');
                }
                $checkoutCartItems = [$allCartItems[$vendorId]];
            }

            [$orders, $lineItems] = $checkoutService->checkout($request, $checkoutCartItems);

            if (config('app.payment_driver') === 'stripe') {
                \Stripe\Stripe::setApiKey(config('app.stripe_secret_key'));

                $session = \Stripe\Checkout\Session::create([
                    'customer_email' => $request->user()->email,
                    'line_items'     => $lineItems,
                    'mode'           => 'payment',
                    'success_url'    => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'     => route('stripe.failure'),
                ]);

                foreach ($orders as $order) {
                    $order->stripe_session_id = $session->id;
                    $order->status = OrderStatusEnum::Processing->value;
                    $order->save();
                }

                DB::commit();
                $cartService->clearCart();

                return redirect($session->url);
            } else {

                // Mock-режим
                foreach ($orders as $order) {
                    $order->stripe_session_id = 'mock_' . uniqid();
                    $order->status = OrderStatusEnum::Paid->value;
                    $order->save();
                }

                DB::commit();
                $cartService->clearCart();

                return redirect()->route('dashboard')
                    ->with('success', 'Demo payment successful!')
                    ->with('from_checkout', true);
            }
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return back()->with('error', $e->getMessage() ?: 'Something went wrong');
        }
    }
}
