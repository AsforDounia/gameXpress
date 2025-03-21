<?php

namespace App\Http\Controllers\Api\V2;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\Product;
use Tests\Feature\ProductTest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="API Endpoints for user authentication"
 * )
 */

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
     * @OA\Post(
     *     path="/api/v2/AddToCart/{product_id}",
     *     operationId="addToCart",
     *     tags={"Cart"},
     *     summary="Add product to cart for authenticated users",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="ID of the product to add to cart",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="cart", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

    public function AddToCart(Request $request, $product_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $productStock = $this->checkStock($product_id, $request->quantity);

        if ($productStock->getData()->status != 'disponible') {
            return $productStock;
        }
        $sessionId = $request->header('X-Session-ID');

        if (Auth::check()) {
            $cartItem = CartItem::where('user_id', Auth::id())
                ->where('product_id', $product_id)
                ->first();
            if ($cartItem) {
                return "the product is already existe";
            } else {
                $cart = CartItem::firstOrCreate([
                    'user_id' => Auth::id(),
                    'product_id' => $product_id,
                    'quantity' => $request->quantity
                ]);
            }
            return ['cart' => $cart];
        }
        $cartItem = CartItem::where('session_id', $sessionId)
            ->where('product_id', $product_id)
            ->first();

        if ($cartItem) {
            return "the product is already existe";
        } else {
            $cart = CartItem::firstOrCreate([
                'session_id' => $sessionId,
                'product_id' => $product_id,
                'quantity' => $request->quantity
            ]);
        }
        return ['cart' => $cart];
    }

    /**
     * @OA\Get(
     *     path="/api/v2/getCart",
     *     operationId="getCart",
     *     tags={"Cart"},
     *     summary="Get current user's cart",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart content returned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="quantity", type="integer"),
     *             )),
     *             @OA\Property(property="total_before_tax", type="number"),
     *             @OA\Property(property="total_tax", type="number"),
     *             @OA\Property(property="total_after_tax", type="number"),
     *             @OA\Property(property="total_discount", type="number"),
     *             @OA\Property(property="total_final", type="number"),
     *             @OA\Property(property="totalItems", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */

    public function getCart(Request $request)
    {
        if (Auth::check()) {
            $cartItems = CartItem::where('user_id', Auth::id())->get();
        } else {
            $sessionId = $request->header('X-Session-ID');

            if (!$sessionId) {
                return ['message' => 'Session ID is required in X-Session-ID header'];
            }

            $cartItems = CartItem::where('session_id', $sessionId)->get();
        }

        if ($cartItems->isEmpty()) {
            return ['message' => 'Cart is empty or session not found'];
        }

        $items = [];

        foreach ($cartItems as $item) {
            $product = $item->product;

            $items[] = [
                'product_id' => $item->product_id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $item->quantity,
            ];
        }
        $totalItems = $cartItems->sum('quantity');
        $totalPrices = Helper::calculateTotalHelper($cartItems);
        // return $totalPrices;
        return [
            'items' => $items,
            // 'totalCart' => $totalPrices,
            'total_before_tax' => $totalPrices['total_before_tax'],
            'total_tax' => $totalPrices['total_tax'],
            'total_after_tax' => $totalPrices['total_after_tax'],
            'total_discount' => $totalPrices['total_discount'],
            'total_final' => $totalPrices['total_final'],
            'totalItems' => $totalItems
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
     * @OA\Post(
     *     path="/api/v2/cart/merge",
     *     operationId="mergeCart",
     *     tags={"Cart"},
     *     summary="Merge guest cart with user cart after login",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Session-ID",
     *         in="header",
     *         required=true,
     *         description="Session ID for the guest user",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart merged successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart merged successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function cartMerge(Request $request)
    {
        $sessionId = $request->header('X-Session-ID');
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
    /**
     * @OA\Post(
     *     path="/api/v2/check-stock/{productId}/{quantity}",
     *     operationId="checkStock",
     *     tags={"Cart"},
     *     summary="Check stock availability of a product",
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="quantity",
     *         in="path",
     *         description="Quantity to check",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock status",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="stock_disponible", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */

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
        $product = Product::where('id', $cart_item->product_id)->firstOrFail();

        if ($product->stock >= $quantity) {
            $cart_item->update(['quantity' => $quantity]);
            $cart_item->save();
            return response()->json(['status' => 'success', 'message' => 'quantité mes a jour avec succees']);
        } else {
            return response()->json(['status' => 'erreur', 'message' => 'quantité insufisant']);
        }
    }
/**
 * @OA\Delete(
 *     path="/api/v2/destroyProductForClient/{productId}",
 *     operationId="removeProductFromCart",
 *     tags={"Cart"},
 *     summary="Remove product from user's cart",
 *     security={{"sanctum": {}}},
 *     @OA\Parameter(
 *         name="productId",
 *         in="path",
 *         description="ID of the product to remove",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="X-Session-ID",
 *         in="header",
 *         required=false,
 *         @OA\Schema(type="string"),
 *         description="Session ID for guests"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product removed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product removed from your cart")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found in cart"
 *     )
 * )
 */


    public function destoryProductFromCart(Request $request, $productId)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $cartItem = CartItem::where('user_id', $userId)->where('product_id', $productId)->first();
        } else {
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
/**
 * @OA\Post(
 *     path="/api/v2/calculateTotalForClient",
 *     operationId="calculateTotalCart",
 *     tags={"Cart"},
 *     summary="Calculate the total price of the cart with tax and discounts",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="X-Session-ID",
 *         in="header",  
 *         required=false,
 *         @OA\Schema(type="string"),
 *         description="Session ID for guests"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Total calculated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="total_before_tax", type="number", example=100.0),
 *             @OA\Property(property="total_tax", type="number", example=20.0),
 *             @OA\Property(property="total_after_tax", type="number", example=120.0),
 *             @OA\Property(property="total_discount", type="number", example=10.0),
 *             @OA\Property(property="total_final", type="number", example=110.0)
 *         )
 *     ),
 *     @OA\Response(response=404, description="Your cart is empty")
 * )
 */


    public function calculateTotalofCart(Request $request)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $cartItems = CartItem::where('user_id', $userId)->with('product')->get();
        } else {
            $sessionId = $request->header('X-Session-ID');
            $cartItems = CartItem::where('session_id', $sessionId)->with('product')->get();
        }

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 404);
        }

        return Helper::calculateTotalHelper($cartItems);
    }
}
