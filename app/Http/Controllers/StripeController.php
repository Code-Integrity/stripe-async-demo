<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller
{

    private function getMockItem()
    {
        return (object)[
            'id' => 99,
            'name' => 'Tolt Defense System - Premium Tier',
            'price' => 5000,
        ];
    }


    public function index()
    {
        $item = $this->getMockItem();
        return view('purchase', compact('item'));
    }


    public function purchase()
    {
        $item = $this->getMockItem();


        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $item->name],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchase.cancel'),

            'metadata' => [
                'item_id' => $item->id,
                'user_id' => 1,
            ],
        ]);


        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        return redirect($session->url, 303);
    }


    public function success(Request $request)
    {
        return "<h3>[Async Demo] Payment Processed on Stripe.</h3><p>Check terminal for asynchronous Webhook capture!</p><a href='/'>Back</a>";
    }


    public function cancel()
    {
        return "<h3>Payment Canceled.</h3><a href='/'>Back</a>";
    }


    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {

            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {

            Log::error('Webhook Error: Invalid Payload');
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {

            Log::error('Webhook Error: Invalid Signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }


        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;


            $itemId = $session->metadata->item_id ?? null;
            $userId = $session->metadata->user_id ?? null;

            Log::info("【非同期決済成功】商品ID: {$itemId} が ユーザーID: {$userId} によって購入され、安全に決済完了しました。");
        }

        return response()->json(['status' => 'success'], 200);
    }
}
