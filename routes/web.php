<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    \Illuminate\Support\Facades\Auth::login(\App\Models\User::first());
    return view('welcome');
});

Route::post('/stripe/webhook', [\App\Http\Controllers\Webhooks\StripeController::class, 'handleWebhook'])
    ->middleware(\Laravel\Cashier\Http\Middleware\VerifyWebhookSignature::class);
