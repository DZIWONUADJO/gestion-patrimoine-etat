<?php

use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/categories', [CategorieController::class, 'index']);
    Route::get('/categories/{categorie}', [CategorieController::class, 'show']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    Route::middleware('can:manage-references')->group(function () {
        Route::post('/categories', [CategorieController::class, 'store']);
        Route::match(['put', 'patch'], '/categories/{categorie}', [CategorieController::class, 'update']);
        Route::post('/services', [ServiceController::class, 'store']);
        Route::match(['put', 'patch'], '/services/{service}', [ServiceController::class, 'update']);
    });
});
