<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
/**
 * @OA\Tag(
 *     name="Categories",
 *     description="Category Management"
 * )
 */

class CategoryController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v1/admin/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="categories", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Requires super_admin or product_manager role"
     *     )
     * )
     */

    public function index()
    {
        // $categories = Category::all();
        // // groub by update_at
        // $categories = $categories->sortByDesc('updated_at')->values()->all();
        // $categories = Category::orderBy('updated_at', 'desc')->get();
        $categories = Category::withCount('subcategories')->orderBy('updated_at', 'desc')->get();
        return response()->json(['categories' => $categories]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/categories",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug"},
     *             @OA\Property(property="name", type="string", example="Action"),
     *             @OA\Property(property="slug", type="string", example="action")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="slug", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
        ]);

        // slug is generated automatically
        $slug = Str::slug($request->name);
        $data['slug'] = $slug;

        $category = Category::create($data);


        return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/categories/{id}",
     *     summary="Get category by ID",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="slug", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function show(string $id)
    {
        // $category = Category::findOrFail($id);
        // category with subcategories
        $category = Category::with('subcategories')->findOrFail($id);
        return response()->json(['category' => $category]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/categories/{id}",
     *     summary="Update category by ID",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Adventure"),
     *             @OA\Property(property="slug", type="string", example="adventure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="slug", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'slug' => 'sometimes|string|unique:categories,slug,' . $id,
        ]);

        $category = Category::findOrFail($id);
        $category->update($data);

        return response()->json(['message' => 'Category updated successfully', 'category' => $category]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/categories/{id}",
     *     summary="Delete category by ID",
     *     tags={"Categories"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
