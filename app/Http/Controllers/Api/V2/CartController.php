<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\Product;
use Tests\Feature\ProductTest;
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
    public function AddToCartGuest(Request $request)
    {
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

        $product = Product::with('productImages')->find($request->product_id);
        // $cartItem = CartItem::create($cartData);

        return [
            'cart_Item' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $request->quantity,
                'image' => $product->productImages->where('is_primary', true)
            ]
        ];
    }

    public function addToCart(Request $request)
    {
        // return $request;
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);
        $productStock = $this->checkStock($request->product_id, $request->quantity);

        if ($productStock['status'] != 1) {
            return $productStock;
        }

        $cartData = [
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ];

        $cartData['user_id'] = Auth::id();
        $cartData['session_id'] = null;

        $cartItem = CartItem::create($cartData);

        $product = Product::with('productImages')->find($request->product_id);

        return [
            'cart_Item' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $cartItem->quantity,
                'image' => $product->productImages->where('is_primary', true)
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
    public function destroy(string $id) {}
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

    public function checkStock($productId, $quantity)
    {

        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['status' => 'introuvable', 'message' => 'Produit introuvable'], 404);
        }
        if ($product->stock < $quantity) {
            return response()->json(['status' => 'insuffisant', 'message' => 'Stock insuffisant', 'stock_disponible' => $product->stock], 200);
        }
        return response()->json(['status' => 'disponible', 'message' => 'Produit en stock'], 200);
    }


    public function modifyQuantityProductInCart() {}
    // public function modifyQuantityProductInCart($product, $cart_items)
    // {
    //     $quantity = $cart_items->quantity;
    // }



    public function destoryProductForClient(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $userId = Auth::id();
        $cartItem = CartItem::where('user_id', $userId)->where('product_id', $request->product_id)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Product not found in cart'], 404);
        }

        $cartItem->delete();
        return response()->json(['message' => 'Product removed from cart'], 200);
    }
}
