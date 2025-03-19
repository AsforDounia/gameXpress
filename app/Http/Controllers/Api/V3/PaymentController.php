<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

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

    public function createCheckoutSession(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $cartItems = CartItem::where('user_id', Auth::id())->get();
        $lineItems = [];

        foreach ($cartItems as $item){
            $product = Product::find($item->product_id);
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name,
                        'images' => [$product->productImages->where('is_primary', true)->first()],
                    ],
                    'unit_amount' => intval($product->price * 100),
                ],
                'quantity' => $item->quantity,
            ];
        }
        $session = StripeSession::create([

            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            // [[
            //     'price_data' => [
            //         'currency' => 'usd',
            //         'product_data' => [
            //             'name' => 'Produit Test',
            //         ],
            //         'unit_amount' => 1000, // 10.00$
            //     ],
            //     'quantity' => 1,
            // ]],
            'mode' => 'payment',
            'success_url' => 'http://127.0.0.1:8000/api/sucess',
            'cancel_url' => 'http://127.0.0.1:8000/api/cancel',
        ]);

        return response()->json([
            'session' => $session
        ]);
    }

}
