<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
    public function webhook()
    {
        return view('stripe.success');
    }
}
