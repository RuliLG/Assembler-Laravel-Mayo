<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::active()->get();
        $categories->loadCount('published_posts');
        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function store(UpsertCategoryRequest $request)
    {
        $validated = $request->validated();

        $category = Category::create($validated);

        return response()->json([
            'status' => 'success',
            'category' => $category,
        ]);
    }

    public function show(Category $category)
    {
        $category->load('published_posts:id,name,published_at');

        return response()->json([
            'category' => $category,
        ]);
    }

    public function update(Category $category, UpsertCategoryRequest $request)
    {
        $validated = $request->validated();

        $category->update($validated);

        return response()->json([
            'status' => 'success',
            'category' => $category,
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json([
            'status' => 'success',
        ]);
    }
}
