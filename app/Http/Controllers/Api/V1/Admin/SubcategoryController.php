<?php

namespace App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $data =$request->validate([
            'name' => 'required|string|max:255|unique:subcategories',
            'category_id' => 'required|exists:categories,id',
        ]);

        $slug = Str::slug($request->name);
        $data['slug'] = $slug;



        $subcategory = Subcategory::create($data);

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
            'name' => 'required|string|max:255|unique:subcategories',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        if ($request->has('name')) {
            $slug = Str::slug($request->name);
            $request['slug'] = $slug;
        }

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
