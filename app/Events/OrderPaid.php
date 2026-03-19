<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OrderPaid
{
    use Dispatchable, SerializesModels;

    public Collection $orders;
    public array $paymentData;

    public function __construct(Collection $orders, array $paymentData = [])
    {
        $this->orders = $orders;
        $this->paymentData = $paymentData;
    }
}
