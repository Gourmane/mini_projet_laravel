<?php
require __DIR__.'/vendor/autoload.php';
$app=require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$stats=['categories'=>App\Models\Category::count(),'products'=>App\Models\Product::count(),'movements'=>App\Models\StockMovement::count(),'units'=>(int)App\Models\Product::sum('quantity'),'inactive'=>App\Models\Product::where('is_active',false)->count(),'category_counts'=>App\Models\Category::withCount('products')->orderBy('id')->get(['id','name'])->toArray()];
echo json_encode($stats,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
