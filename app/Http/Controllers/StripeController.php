<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderViewResource;
use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

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

    public function webhook(Request $request, StripeService $webhookService)
    {
        $webhookSecret = config('app.stripe_webhook_secret');
        $payload       = $request->getContent();
        $sigHeader     = $request->header('Stripe-Signature');

        Log::info('WEBHOOK HIT!', [
            'headers' => $request->headers->all(),
            'content' => $payload,
        ]);

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException | SignatureVerificationException $e) {
            Log::error($e);
            return response('Invalid payload', 400);
        }

        $stripe = new StripeClient(config('app.stripe_secret_key'));

        switch ($event->type) {
            case 'checkout.session.completed':
                $webhookService->handleCheckoutSessionCompleted(
                    $event->data->object->toArray()
                );
                break;

            case 'charge.updated':
                $webhookService->handleChargeUpdated(
                    $event->data->object->toArray(),
                    $stripe
                );
                break;

            default:
                Log::info('Unhandled event type', ['type' => $event->type]);
        }

        return response('', 200);
    }
}
