<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:200',
            'category_id' => 'nullable|integer|exists:categories,id',
            'stock_status' => ['nullable', Rule::in(['healthy', 'low', 'out', 'inactive'])],
            'active' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = Product::with('category')->orderBy('name');

        if (!empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            // Group OR conditions so category and stock filters still apply.
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', $search)
                    ->orWhere('sku', 'like', $search)
                    ->orWhere('brand', 'like', $search);
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        $status = $filters['stock_status'] ?? '';
        if ($status === 'healthy') {
            $query->whereColumn('quantity', '>', 'min_stock');
        } elseif ($status === 'low') {
            $query->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'min_stock');
        } elseif ($status === 'out') {
            $query->where('quantity', 0);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function show(Product $product)
    {
        $product->load('category');
        $product->load(['movements' => function ($query) {
            $query->with('user:id,name')->latest('id')->limit(10);
        }]);

        return response()->json(['data' => $product]);
    }

    public function store(ProductRequest $request)
    {
        // ProductRequest also validates image_url; Eloquent includes it in the JSON.
        $product = Product::create($request->validated());

        return response()->json(['data' => $product->fresh()->load('category')], 201);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json(['data' => $product->fresh()->load('category')]);
    }

    public function status(Request $request, Product $product)
    {
        $values = $request->validate(['is_active' => 'required|boolean']);
        $product->update($values);

        return response()->json(['data' => $product->fresh()->load('category')]);
    }
}

