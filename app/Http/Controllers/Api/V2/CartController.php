<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
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
    public function AddToCartGuest(Request $request){
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $productStock = $this->checkStock($request->product_id,$request->quantity);
        
        if($productStock->getData()->status != 'disponible'){
            return $productStock;
        }
        // return $request;

        // $sessionId = session()->getId();
        $sessionId = $request->header('X-Session-ID');
        $cart = session()->get('cart', []);

        $product = Product::with('productImages')->find($request->product_id);
        
        if (isset($cart[$request->product_id])) {
            $cart[$request->product_id]['quantity'] += $request->quantity;
        } else {
            $cart[$request->product_id] = [
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'session_id' => $sessionId,
                'user_id' => null, 
            ];
        }

        return [ 
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'name' => $product->name,
            'price' => $product->price,
            'image' => $product->productImages->where('is_primary',true)
            ];
    }

        public function AddToCart(Request $request)
        {
            // return $request;
            $request->validate([
                'product_id' => 'required',
                'quantity' => 'required|integer|min:1',
            ]);
            $productStock = $this->checkStock($request->product_id,$request->quantity);

            if($productStock->getData()->status != 'disponible'){
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


    public function modifyQuantityProductInCart()
    {

    }
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

    public function destoryProductForGuet(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $sessionId = $request->header('X-Session-ID');
        $cart = session()->get('cart', []);
        if ($cart[$request->product_id]['session_id'] == $sessionId) {
            unset($cart[$request->product_id]);
            session()->put('cart', $cart);
            return response()->json(['message' => 'Product removed from cart'], 200);
        }
        else{
            return response()->json(['message' => 'Product not found in your cart']);
        }
    }



}
