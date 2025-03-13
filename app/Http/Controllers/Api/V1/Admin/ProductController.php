<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $products = Product::all();
        $products = Product::with(['subcategory', 'productImages'])->get();
        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required|in:available,out_of_stock',
            'subcategory_id' => 'required|exists:subcategories,id',
            'images' => 'array'
        ]);

        $product = Product::create($request->only(['name', 'slug', 'price', 'stock', 'status', 'subcategory_id']));
        $productImages = [];
        if (isset($request->images)) {
            foreach ($request->images as $image) {
                $imagePath = $image->store('product_images');
                $productImages[] = new ProductImage([
                    'image_url' => $imagePath,
                    'is_primary' => true,
                ]);
            }
            $product->productImages()->saveMany($productImages); // Save the images
        }

        return response()->json(['message' => 'Product created successfully', 'product' => $product->load('productImages')], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['subcategory', 'productImages'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:products,slug,' . $id,
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'status' => 'sometimes|boolean',
            'subcategory_id' => 'sometimes|exists:subcategories,id',
        ]);

        $product->update($request->all());

        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
