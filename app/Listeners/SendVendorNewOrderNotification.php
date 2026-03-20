<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\NewOrderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendVendorNewOrderNotification implements ShouldQueue
{
    public $queue = 'emails';
    public $connection = 'redis';

    public function handle(OrderPaid $event): void
    {
        foreach ($event->orders as $order) {
            try {
                Mail::to($order->vendorUser)->send(new NewOrderMail($order));
                Log::info('Vendor email queued', ['order' => $order->id]);
            } catch (\Exception $e) {
                Log::error('Vendor email failed', ['order' => $order->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
