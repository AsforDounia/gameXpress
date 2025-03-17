<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Tests\Feature\ProductTest;

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

    public function checkStock(Request $request)
    {
        $productId = $request->input('productId');
        $quantity = $request->input('quantity');
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
