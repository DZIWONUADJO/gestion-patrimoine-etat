<?php

use App\Http\Controllers\Web\BienController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/connexion', [SessionController::class, 'create'])->name('login');
Route::post('/connexion', [SessionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('login.store');

Route::middleware(['auth', 'web.active'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/deconnexion', [SessionController::class, 'destroy'])->name('logout');

    Route::get('/biens', [BienController::class, 'index'])->name('biens.index');

    Route::middleware('can:manage-biens')->group(function () {
        Route::get('/biens/nouveau', [BienController::class, 'create'])->name('biens.create');
        Route::post('/biens', [BienController::class, 'store'])->name('biens.store');
    });

    Route::get('/biens/{bien}', [BienController::class, 'show'])->name('biens.show');
    Route::get('/biens/{bien}/documents/{document}/telecharger', [BienController::class, 'downloadDocument'])
        ->name('biens.documents.download');

    Route::middleware('can:manage-biens')->group(function () {
        Route::get('/biens/{bien}/modifier', [BienController::class, 'edit'])->name('biens.edit');
        Route::match(['put', 'patch'], '/biens/{bien}', [BienController::class, 'update'])->name('biens.update');
        Route::post('/biens/{bien}/documents', [BienController::class, 'storeDocument'])
            ->name('biens.documents.store');
        Route::delete('/biens/{bien}/documents/{document}', [BienController::class, 'destroyDocument'])
            ->name('biens.documents.destroy');
    });

    Route::middleware('can:archive-biens')->group(function () {
        Route::post('/biens/{bien}/archiver', [BienController::class, 'archiver'])->name('biens.archive');
        Route::post('/biens/{bien}/restaurer', [BienController::class, 'restaurer'])->name('biens.restore');
    });
});
