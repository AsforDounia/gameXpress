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

        if($productStock['status'] != 1){
            return $productStock;
        }

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
                'image' => $product->productImages->where('is_primary',true)
            ];
        }

        return ['cart_Item' => $cart];
    }

        public function addToCart(Request $request)
        {
            // return $request;
            $request->validate([
                'product_id' => 'required',
                'quantity' => 'required|integer|min:1',
            ]);
            $productStock = $this->checkStock($request->product_id,$request->quantity);

            if($productStock['status'] != 1){
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

    public function checkStock($productId,$quantity)
    {
        // $productId = $request->input('productId');
        // $quantity = $request->input('quantity');
        // return $productId;
        $product = Product::find($productId);

        if (!$product) {
            return ['status'=>'introvable','message' => 'produit introvable' ];
        } elseif ($product->stock < $quantity) {
            return ['status'=>'insufisant','message' => 'stock insufisant', 'stock'=>$product->stock];
        } else {
            return  ['status'=>true ,'message' => 'produit trouvable'];
        }
    }
}
