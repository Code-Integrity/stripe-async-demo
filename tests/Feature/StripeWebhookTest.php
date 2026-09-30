<?php

use Illuminate\Support\Facades\Log;

beforeEach(function () {

    config(['services.stripe.webhook_secret' => 'whsec_test_secret']);
});


test('it aborts 400 when Stripe-Signature header is missing', function () {
    $response = $this->postJson(route('stripe.webhook'), [
        'type' => 'checkout.session.completed'
    ]);

    $response->assertStatus(400);
});


test('it aborts 400 when Stripe-Signature is invalid', function () {
    $response = $this->withHeaders([
        'Stripe-Signature' => 't=123456789,v1=bad_signature_packet'
    ])->postJson(route('stripe.webhook'), [
        'type' => 'checkout.session.completed'
    ]);

    $response->assertStatus(400);
});


test('it successfully processes checkout session completed event with valid signature simulation', function () {
    Log::shouldReceive('info')
        ->once()
        ->withArgs(fn($message) => str_contains($message, '【非同期決済成功】商品ID: 99'));

    Log::shouldReceive('info')->zeroOrMoreTimes();



    $payload = [
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'metadata' => [
                    'item_id' => 99,
                    'user_id' => 1
                ]
            ]
        ]
    ];


    $time = time();
    $signedPayload = $time . '.' . json_encode($payload);
    $signature = hash_hmac('sha256', $signedPayload, 'whsec_test_secret');
    $sigHeader = "t={$time},v1={$signature}";

    $response = $this->withHeaders([
        'Stripe-Signature' => $sigHeader
    ])->postJson(route('stripe.webhook'), $payload);

    $response->assertStatus(200);
    $response->assertJson(['status' => 'success']);
});
