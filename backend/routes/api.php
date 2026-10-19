<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ProductController, CategoryController, MovementController, DashboardController};
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => response()->json(['data' => $request->user()]));
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/movements', [MovementController::class, 'index']);
    Route::post('/movements', [MovementController::class, 'store']);
    Route::middleware('admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::patch('/products/{product}/status', [ProductController::class, 'status']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
    });
});
