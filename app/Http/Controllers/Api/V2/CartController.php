<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
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
    public function cartMerge(Request $request)
    {
        $sessionId = $request->cookie('laravel_session');
        $user = $request->user();

        $sessionItems = CartItem::whereNotNull('session_id')
            ->where('session_id', $sessionId)
            ->get();

        foreach ($sessionItems as $sessionItem) {
             $userOrder = CartItem::where('user_id', $user->id)
                ->where('product_id', $sessionItem->product_id)
                ->first();

            if ($userOrder) {
                $userOrder->quantity += $sessionItem->quantity;
                $userOrder->save();
            } else {
                CartItem::create([
                    'product_id' => $sessionItem->product_id,
                    'user_id' => $user->id,
                    'session_id' => null,
                    'quantity' => $sessionItem->quantity,
                ]);
            }

            $sessionItem->delete();
        }

        return response()->json([
            'message' => 'Cart merged successfully!',
        ]);
    }
}
