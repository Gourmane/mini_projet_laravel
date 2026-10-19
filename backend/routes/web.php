<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/', fn () => response()->json(['name' => 'IT Stock Manager API']));
