<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'stats']);
    Route::get('/activity-log', [DashboardController::class, 'activityLog']);
    
    // Items
    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index']);
        Route::get('/low-stock', [ItemController::class, 'lowStock']);
        Route::get('/categories', [ItemController::class, 'categories']);
        Route::get('/{id}', [ItemController::class, 'show']);
        Route::post('/', [ItemController::class, 'store']);
        Route::put('/{id}', [ItemController::class, 'update']);
        Route::delete('/{id}', [ItemController::class, 'destroy']);
    });
    
    // Transactions
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
    });
    
    // Reports (Admin only)
    Route::prefix('reports')->group(function () {
        Route::get('/items/pdf', [ReportController::class, 'exportItems']);
        Route::get('/transactions/pdf', [ReportController::class, 'exportTransactions']);
        Route::get('/stock/pdf', [ReportController::class, 'exportStock']);
    });
    
    // Users (Admin only)
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
    
});