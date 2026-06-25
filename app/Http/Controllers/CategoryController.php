<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // সব ক্যাটাগরি নিয়ে এসে রিসোর্স কালেকশনে রূপান্তর করে রিটার্ন করবে
        return response()->json([
            'success' => true,
            'message' => 'Categories fetched successfully',
            'data' => CategoryResource::collection(Category::all())->resolve(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ইনপুট ভ্যালিডেশন
        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name|max:255',
        ]);

        // নামের ওপর ভিত্তি করে স্লাগ তৈরি
        $validated['slug'] = Str::slug($validated['name']);

        // ডাটাবেজে সেভ
        $category = Category::create($validated);

        // নতুন তৈরি হওয়া ক্যাটাগরিটি রিসোর্সের মাধ্যমে রিটার্ন
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => (new CategoryResource($category))->resolve(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'message' => 'Category fetched successfully',
            'data' => (new CategoryResource($category))->resolve(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // ইনপুট ভ্যালিডেশন (নামটি ইউনিক হতে হবে তবে নিজের আইডির জন্য ছাড় থাকবে)
        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id . '|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => (new CategoryResource($category))->resolve(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
