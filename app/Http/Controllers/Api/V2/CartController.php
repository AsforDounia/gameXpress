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
    public function modifyQuantityProductInCart(Request $request, $cart_itemId)
    {
        
        $quantity = $request->input('quantity');
        $cart_item = CartItem::findOrfail($cart_itemId);
        $product = Product::where('id',$cart_item->product_id)->firstOrFail();

        if($product->stock >= $quantity){
            $cart_item->update(['quantity' => $quantity]);
            $cart_item->save();
            return response()->json(['status' => 'success', 'message' => 'quantité mes a jour avec succees']);
        }else{
            return response()->json(['status' => 'erreur', 'message' => 'quantité insufisant']);
        }
    } 

   
   
}
