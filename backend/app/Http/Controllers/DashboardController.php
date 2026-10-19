<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;

class DashboardController
{
    public function index()
    {
        $totalProducts = Product::count();
        $lowStock = Product::where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'min_stock')
            ->count();
        $outOfStock = Product::where('quantity', 0)->count();
        $recentMovements = StockMovement::with(['product:id,name,sku', 'user:id,name'])
            ->latest('created_at')
            ->latest('id')
            ->limit(5)
            ->get();

        return response()->json(['data' => [
            'total_products' => $totalProducts,
            'total_units' => (int) Product::sum('quantity'),
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'healthy_stock' => $totalProducts - $lowStock - $outOfStock,
            'movement_count' => StockMovement::count(),
            'recent_movements' => $recentMovements,
        ]]);
    }
}

