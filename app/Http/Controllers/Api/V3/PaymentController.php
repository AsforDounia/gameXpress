<?php

namespace App\Http\Controllers\Api\V3;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\SuccessNotification;
use Stripe\Webhook;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
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
        $payment = Payment::with('order.user')->find($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Paiement non trouvé.'
            ], 404);
        }
        return response()->json([
            'payment' => $payment
        ]);
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

    public function TraitementPaiements(Request $request) {}


    public function createCheckoutSession(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $cartItems = CartItem::where('user_id', Auth::id())->get();

        $lineItems = [];
        $totalPrice = Helper::calculateTotalHelper($cartItems);
        foreach ($cartItems as $cart) {
            $product = Product::find($cart->product_id);
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name,
                        // 'images' => [$product->productImages->where('is_primary', true)->first()],
                    ],
                    'unit_amount' => intval($product->price * 100),
                ],
                'quantity' => $cart->quantity,
            ];
        }

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => 'http://127.0.0.1:8000/api/sucess',
            'cancel_url' => 'http://127.0.0.1:8000/api/cancel',

            'custom_text' => [
                'submit' => [
                    'message' => "Total à payer : " . $totalPrice['total_final']
                ]
            ]
        ]);

        // ============================================================

        //paid=payé
        //thanita tsaajalar thays l payment najthith
        $order = Order::create([
            'user_id' => Auth::id(),
            'total_price' => $session->amount_total / 100,
        ]);
        $pay = Payment::create([
            'order_id' => $order->id,
            'payment_type' => 'stripe',
            'status' => 'pending',
            'amount' => $session->amount_total / 100,
            'session_id' => $session->id,
        ]);
        // thanita ntawid minghari thi panier

        $cartItems = CartItem::where('user_id', Auth::id())->get();
        foreach ($cartItems as $item) {
            $product = Product::find($item->product_id);

            if ($product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $product->price * $item->quantity,
                ]);
            }

            // thanita mashakhth zi l panier porki safi sghikhth
            $item->delete();
        }

        return response()->json([
            'session' => $session,
            'final_price' => $totalPrice['total_final']
        ]);
    }

    public function success(Request $request)
    {

        // thanita ntawid session id bach athanar tawi  data
        $sessionId = $request->header('X-Stripe-Session-Id');
        //thanita akhmini tawyard chanhajat n l compte ino
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = \Stripe\Checkout\Session::retrieve($sessionId);

        $payment = Payment::where('session_id', $sessionId)->first();

        if ($payment) {
            $payment->update(['status' => 'successful']);
            $order = $payment->order;

            if ($order) {
                $order->update(['status' => 'in progress']);
            }
        }
        //thanita 3awth daga itasad zi stript
        if ($session->payment_status === 'paid') {
            $customer = $order->user;
            $users = User::all();
            $admins = $users->filter(function ($user) {
                return $user->hasRole('super_admin');
            });
            
            $recipients = $admins->push($customer);

            Notification::send($recipients, new SuccessNotification($order));
            return response()->json([
                'message' => 'Paiement réussi',
                'session' => $session,
            ]);
        } else {
            return response()->json([
                'message' => 'Le paiement n est  pas reussi ',
                'session' => $session
            ]);
        }
    }
}
