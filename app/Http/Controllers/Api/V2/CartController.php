<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function AddToCartGuest(Request $request){
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cartData = [
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ];

        $cartData['user_id'] = null;
        $cartData['session_id'] = Session::getId();

        CartItem::create($cartData);

        return response()->json(['message' => 'Item added to cart successfully.']);

        return [
            'product_id' => $product->id, 
            'productImage' => $productImage,
            'quantity' => $quantity
        ];
    }

        public function addToCart(Request $request)
        {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cartData = [
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ];

            $cartData['user_id'] = Auth::id();
            $cartData['session_id'] = null;

            $cartItem = CartItem::create($cartData);

            $product = Product::with('images')->find($request->product_id);

            return ['message' => 'Item added to cart successfully.',
                'cart_Item' => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'image' => $product->productImages->where('is_primary',true)
                ],
            ];
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
}
