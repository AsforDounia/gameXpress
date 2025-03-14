<?php

namespace App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcategories = Subcategory::with('category')->get();
        return response()->json(['subcategories' => $subcategories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:subcategories,slug',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subcategory = Subcategory::create($request->all());

        return response()->json(['message' => 'Subcategory created successfully', 'subcategory' => $subcategory], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subcategory = Subcategory::with('category')->find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'Subcategory not found'], 404);
        }

        return response()->json(['subcategory' => $subcategory]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subcategory = Subcategory::find($id);

        if (!$subcategory) {
            return response()->json(['message' => 'Subcategory not found'], 404);
        }
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:subcategories,slug',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $subcategory->update($request->all());

        return response()->json(['message' => 'Subcategory updated successfully', 'subcategory' => $subcategory]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subcategory = Subcategory::find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'Subcategory not found'], 404);
        }

        $subcategory->delete();

        return response()->json(['message' => 'Subcategory deleted successfully']);
    }
}
