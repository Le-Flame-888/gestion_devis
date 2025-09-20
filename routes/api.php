<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\QuoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Routes d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par authentification Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('quotes', QuoteController::class);
    
    // Additional quote endpoints for product management
    Route::post('quotes/{quote}/products', [QuoteController::class, 'addProduct']);
    Route::delete('quotes/{quote}/products/{detail}', [QuoteController::class, 'removeProduct']);
});
