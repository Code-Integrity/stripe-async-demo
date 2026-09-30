<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;


Route::get('/', [StripeController::class, 'index'])->name('purchase.index');


Route::post('/purchase', [StripeController::class, 'purchase'])
    ->name('purchase.checkout')
    ->middleware('throttle:stripe-checkout');


Route::get('/purchase/success', [StripeController::class, 'success'])->name('purchase.success');
Route::get('/purchase/cancel', [StripeController::class, 'cancel'])->name('purchase.cancel');


Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook'])->name('stripe.webhook');
