<?php

namespace App\Http\Controllers;

use App\Models\cart_items;
use Illuminate\Http\Request;

class CartItemsController extends Controller
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
    public function show(cart_items $cart_items)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, cart_items $cart_items)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(cart_items $cart_items)
    {
            // Find the cart item
            $cartItem = CartItem::find($id);

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            if ($cartItem->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }


            // Delete the cart item
            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully'
            ]);

    }
}
