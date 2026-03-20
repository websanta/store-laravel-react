<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\CheckoutCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCustomerOrderConfirmation implements ShouldQueue
{
    public $queue = 'emails';
    public $connection = 'redis';

    public function handle(OrderPaid $event): void
    {
        try {
            $firstOrder = $event->orders->first();
            if ($firstOrder) {
                Mail::to($firstOrder->user)->send(new CheckoutCompleted($event->orders));
                Log::info('Customer email queued');
            }
        } catch (\Exception $e) {
            Log::error('Customer email failed', ['error' => $e->getMessage()]);
        }
    }
}
