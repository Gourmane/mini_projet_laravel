<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('id')->get();

        return response()->json(['data' => $categories]);
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json(['data' => $category->loadCount('products')], 201);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return response()->json(['data' => $category->loadCount('products')]);
    }
}

