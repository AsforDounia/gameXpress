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


    // public function AddToCartGuest(Request $request){
    //     $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'quantity' => 'required|integer|min:1'
    //     ]);

    //     $productStock = $this->checkStock($request->product_id,$request->quantity);

    //     if($productStock->getData()->status != 'disponible'){
    //         return $productStock;
    //     }
    //     // return $request;


    //     $product = Product::with('productImages')->find($request->product_id);

    //     if (isset($cart[$request->product_id])) {
    //         return "The product already exists in the cart.";
    //     } else {
    //         $cart[$request->product_id] = [
    //             'product_id' => $product->id,
    //             'quantity' => $request->quantity,
    //             'session_id' => $sessionId,
    //             'user_id' => null,
    //         ];
    //     }
    //     session()->put('cart',$cart);

    //     return [
    //         'product_id' => $product->id,
    //         'quantity' => $request->quantity,
    //         'name' => $product->name,
    //         'price' => $product->price,
    //         'image' => $product->productImages->where('is_primary',true)
    //         ];
    // }


    public function AddToCart(Request $request,$product_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $productStock = $this->checkStock($product_id,$request->quantity);

        if($productStock->getData()->status != 'disponible'){
            return $productStock;
        }
        $sessionId = $request->header('X-Session-ID');

        if (Auth::check()){
                    $cart = CartItem::firstOrCreate([
                        'user_id' => Auth::id(),
                        'product_id' => $product_id,
                        'quantity' => $request->quantity
                    ]);
                return ['cart' => $cart];
            }

            $cart = CartItem::firstOrCreate([
                'session_id' => $sessionId,
                'product_id' => $product_id,
                'quantity' => $request->quantity
            ]);
            return ['cart' => $cart];
    }

    public function getCartGuest()
    {
            $cart = session()->get('cart', []);
        return ['cart' => $cart];
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



    public function destoryProductFromCart(Request $request,$productId)
    {
        if (Auth::check()){
            $userId = Auth::id();
            $cartItem = CartItem::where('user_id', $userId)->where('product_id', $productId)->first();
        }
        else{
            $sessionId = $request->header('X-Session-ID');
            $cartItem = CartItem::where('session_id', $sessionId)->where('product_id ', $productId)->first();
        }

        if (!$cartItem) {
            return response()->json(['message' => 'Product not found in cart'], 404);
        }

        $cartItem->delete();
        return response()->json([
            'message' => 'Product removed from your cart',
            'yourCart' => CartItem::where('user_id', $userId)->get(),
        ], 200);
    }

    // public function destroyProductForGuet(Request $request , $productId)
    // {

    //     $sessionId = $request->header('X-Session-ID');

    //     $cart = Session::get('cart');

    //     if ($cart[$productId]['session_id'] == $sessionId) {
    //         unset($cart[$productId]);
    //         session()->put('cart', $cart);
    //         return response()->json([
    //             'message' => 'Product removed from your cart',
    //             'yourCart' => session()->get('cart', []),
    //         ], 200);
    //     }
    //     else{
    //         return response()->json(['message' => 'Product not found in your cart']);
    //     }
    // }


    public function calculateTotalofCart(Request $request)
    {
        if (Auth::check()){
            $userId = Auth::id();
            $cartItems = CartItem::where('user_id', $userId)->with('product')->get();
        }
        else{
            $sessionId = $request->header('X-Session-ID');
            $cartItems = CartItem::where('session_id', $sessionId)->with('product')->get();
        }

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 404);
        }

        $totalBeforeTax = 0;
        $totalTax = 0;
        $totalAfterTax = 0;
        $totalDiscount = 0;

        $tvaRate = 0.20;

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            $productTotal = $product->price * $cartItem->quantity;
            $totalBeforeTax += $productTotal;
            $totalTax += $productTotal * $tvaRate;
            $discount = $product->remise ;
            $totalDiscount += $productTotal * ($discount / 100);
            $totalAfterTax += $productTotal + ($productTotal * $tvaRate) - ($productTotal * ($discount / 100));
        }


        return response()->json([
            'total_before_tax' =>$totalBeforeTax,
            'total_tax' =>$totalTax,
            'total_after_tax' =>$totalAfterTax,
            'total_discount' =>$totalDiscount,
            'total_final' =>$totalAfterTax - $totalDiscount
        ]);
    }


    // public function calculateTotalForGuest(Request $request)
    // {
    //     $sessionId = $request->header('X-Session-ID');

    //     $cart = Session::get('cart');
    //     if (!$cart) {
    //         return response()->json(['message' => 'The cart is empty'], 404);
    //     }

    //     $cartItems = array_filter($cart, function ($cartItem) use ($sessionId) {
    //         return $cartItem['session_id'] === $sessionId;
    //     });

    //     if (empty($cartItems)) {
    //         return response()->json(['message' => 'Your cart is empty'], 404);
    //     }

    //     return $this->calculateTotalHelper($cartItems);
    // }

    // public function calculateTotalHelper($cartItems)
    // {
    //     $totalBeforeTax = 0;
    //     $totalTax = 0;
    //     $totalAfterTax = 0;
    //     $totalDiscount = 0;

    //     $tvaRate = 0.20;

    //     foreach ($cartItems as $cartItem) {
    //         $product = $cartItem->product;
    //         $productTotal = $product->price * $cartItem->quantity;
    //         $totalBeforeTax += $productTotal;
    //         $totalTax += $productTotal * $tvaRate;
    //         $discount = $product->remise ;
    //         $totalDiscount += $productTotal * ($discount / 100);
    //         $totalAfterTax += $productTotal + ($productTotal * $tvaRate) - ($productTotal * ($discount / 100));
    //     }


    //     return response()->json([
    //         'total_before_tax' =>$totalBeforeTax,
    //         'total_tax' =>$totalTax,
    //         'total_after_tax' =>$totalAfterTax,
    //         'total_discount' =>$totalDiscount,
    //         'total_final' =>$totalAfterTax - $totalDiscount
    //     ]);
    // }

}
