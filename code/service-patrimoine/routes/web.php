<?php

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
});
