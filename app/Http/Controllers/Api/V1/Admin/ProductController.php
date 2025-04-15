<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['subcategory', 'productImages'])->orderBy('updated_at', 'desc')->get();
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
            'name' => 'required|string|max:255|unique:products',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'subcategory_id' => 'required|exists:subcategories,id',
            'primary_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif',
        ]);

        // slug generate automatically from the name
        $slug = Str::slug($request->name);
        $request->merge(['slug' => $slug]);


        $status = $request->stock > 0 ? 'available' : 'out_of_stock';
        // $product = Product::create($request->only(['name', 'slug', 'price', 'stock', 'subcategory_id']) + ['status' => $status]);
        $product = Product::create(
            $request->except(['images', 'primary_image']) + ['status' => $status]
        );
        // $productImages = [];
        // if ($request->hasFile('images')) {
        //     foreach ($request->images as $index => $image) {
        //         $imagePath = $image->store('product_images', 'public');
        //         $productImages[] = new ProductImage([
        //             'image_url' => $imagePath,
        //             'is_primary' => $index === 0,
        //         ]);
        //     }
        //     $product->productImages()->saveMany($productImages);
        // }


        if ($request->hasFile('primary_image') || $request->hasFile('images')) {
            $newImages = [];

            $isPrimary = true;

            if ($request->hasFile('primary_image')) {
                $isPrimary = false;

                $product->productImages()->update(['is_primary' => false]);
                $image = $request->file('primary_image');
                $path = $image->store('product_images', 'public');
                $newImages[] = [
                    'image_url' => $path,
                    'is_primary' => true,
                ];
            }

            if ($request->hasFile('images')) {
                foreach ($request->images as $index => $imageFile) {
                    $path = $imageFile->store('product_images', 'public');
                    $newImages[] = [
                        'image_url' => $path,
                        'is_primary' => ($isPrimary && $index === 0) ? true : false,
                    ];
                }
            }
            $product->productImages()->createMany($newImages);
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

        return response()->json([
            'product' => $product
        ], 200);
    }
    // ----------------------------- update here ---------------------------
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // return response()->json([
        //     'name' => $request->input('name'),
        //     'slug' => $request->input('slug'),
        //     'price' => $request->input('price'),
        //     'stock' => $request->input('stock'),
        //     'subcategory_id' => $request->input('subcategory_id'),
        //     'primary_image' => $request->file('primary_image') ? 'primary_image received' : null,
        //     'images' => $request->file('product_images') ?? [],
        //     'deleted_images' => $request->input('deleted_images') ?? [],
        // ]);
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('products', 'slug')->ignore($product->id)
            ],
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'subcategory_id' => 'sometimes|exists:subcategories,id',
            'deleted_images.*' => 'sometimes',
            'primary_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif',
        ]);


        if ($request->has('stock')) {
            $status = $request->stock > 0 ? 'available' : 'out_of_stock';
            $request->merge(['status' => $status]);
        }

        // $product->update($request->only(['name', 'slug', 'price', 'stock', 'status', 'subcategory_id']));
        $product->update($request->except(['images']));

        if ($request->deleted_images) {
            foreach ($request->deleted_images as $image) {
                Storage::disk('public')->delete($image);
            }
            $product->productImages()->whereIn('image_url', $request->deleted_images)->delete();
        }

        $newImages = [];

        if ($request->hasFile('primary_image')) {
            $oldPrimary = $product->productImages()->where('is_primary', true)->first();

            if ($oldPrimary) {
                Storage::disk('public')->delete($oldPrimary->image_url);
                $oldPrimary->delete();
            }

            $image = $request->file('primary_image');
            $path = $image->store('product_images', 'public');

            $newImages[] = [
                'image_url' => $path,
                'is_primary' => true,
            ];
        }


        if ($request->hasFile('images')) {
            // return 'we have images!!!';
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('product_images', 'public');

                $newImages[] = [
                    'image_url' => $path,
                    'is_primary' => false,
                ];
            }
        }

        if (!empty($newImages)) {
            $product->productImages()->createMany($newImages);
        }

        $product = $product->load('productImages');
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

        $product->productImages()->delete();
        $product->delete();


        return response()->json(['message' => 'Product deleted successfully']);
    }
}
