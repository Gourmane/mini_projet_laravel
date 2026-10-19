<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovementRequest;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MovementController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'type' => ['nullable', Rule::in(['IN', 'OUT'])],
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = StockMovement::with(['product:id,name,sku,category_id', 'user:id,name'])
            ->latest('created_at')
            ->latest('id');

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function store(MovementRequest $request, StockService $stock)
    {
        $data = $request->validated();
        // Trust the logged-in user, never an ID supplied by the browser.
        $movement = $stock->record(
            $data['product_id'],
            $request->user(),
            $data['type'],
            $data['quantity'],
            $data['note'] ?? null,
        );

        return response()->json([
            'data' => $movement->load(['product.category', 'user:id,name']),
            'message' => 'Mouvement enregistré.',
        ], 201);
    }
}

