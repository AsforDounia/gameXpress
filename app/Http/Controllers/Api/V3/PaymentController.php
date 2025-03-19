<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Webhook;



class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function handleWebhook(Request $request)
{
    $payload = $request->getContent();
    $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');
    $secret = env('STRIPE_WEBHOOK_SECRET');

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $secret
        );
    } catch(\Exception $e) {
        return response()->json(['error' => 'Webhook Error'], 400);
    }

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        $orderId = $session->metadata->order_id ?? null;
        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                Payment::create([
                    'order_id' => $order->id,
                    'payment_type' => 'stripe_checkout',
                    'status' => 'réussi',

                    'transaction_id' => $session->payment_intent,
                ]);
                //enum('pending','in progress','shipped','canceled')
                $order->update(['status' => 'shipped']);
            }
        }
    }

    return response()->json(['status' => 'success']);
}
    public function TraitementPaiements(Request $request){
      
        
    }
}
