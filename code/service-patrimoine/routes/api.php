<?php

use App\Http\Controllers\Api\BienController;
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

    Route::get('/biens', [BienController::class, 'index']);
    Route::get('/biens/{bien}', [BienController::class, 'show']);
    Route::get('/biens/{bien}/documents/{document}/telecharger', [BienController::class, 'downloadDocument'])
        ->name('biens.documents.download');

    Route::middleware('can:manage-biens')->group(function () {
        Route::post('/biens', [BienController::class, 'store']);
        Route::match(['put', 'patch'], '/biens/{bien}', [BienController::class, 'update']);
        Route::post('/biens/{bien}/documents', [BienController::class, 'storeDocument']);
        Route::delete('/biens/{bien}/documents/{document}', [BienController::class, 'destroyDocument']);
    });

    Route::middleware('can:archive-biens')->group(function () {
        Route::post('/biens/{bien}/archiver', [BienController::class, 'archiver']);
        Route::post('/biens/{bien}/restaurer', [BienController::class, 'restaurer']);
    });

    Route::middleware('can:manage-references')->group(function () {
        Route::post('/categories', [CategorieController::class, 'store']);
        Route::match(['put', 'patch'], '/categories/{categorie}', [CategorieController::class, 'update']);
        Route::post('/services', [ServiceController::class, 'store']);
        Route::match(['put', 'patch'], '/services/{service}', [ServiceController::class, 'update']);
    });
});
