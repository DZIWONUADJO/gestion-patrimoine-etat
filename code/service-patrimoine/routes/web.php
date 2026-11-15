<?php

use App\Http\Controllers\Web\AffectationController;
use App\Http\Controllers\Web\BienController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InventaireController;
use App\Http\Controllers\Web\SessionController;
use App\Http\Controllers\Web\SuiviController;
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
        Route::post('/biens/{bien}/affectations', [AffectationController::class, 'store'])
            ->name('affectations.store');
        Route::post('/biens/{bien}/documents', [BienController::class, 'storeDocument'])
            ->name('biens.documents.store');
        Route::delete('/biens/{bien}/documents/{document}', [BienController::class, 'destroyDocument'])
            ->name('biens.documents.destroy');
    });

    Route::middleware('can:archive-biens')->group(function () {
        Route::post('/biens/{bien}/archiver', [BienController::class, 'archiver'])->name('biens.archive');
        Route::post('/biens/{bien}/restaurer', [BienController::class, 'restaurer'])->name('biens.restore');
    });

    Route::get('/mouvements', [AffectationController::class, 'index'])->name('mouvements.index');
    Route::post('/mouvements/{affectation}/annuler', [AffectationController::class, 'annuler'])
        ->name('affectations.annuler');

    Route::middleware('can:validate-affectations')->group(function () {
        Route::post('/mouvements/{affectation}/valider', [AffectationController::class, 'valider'])
            ->name('affectations.valider');
        Route::post('/mouvements/{affectation}/rejeter', [AffectationController::class, 'rejeter'])
            ->name('affectations.rejeter');
    });

    Route::middleware('can:manage-biens')->group(function () {
        Route::post('/biens/{bien}/etat', [SuiviController::class, 'changerEtat'])
            ->name('biens.etat');
        Route::post('/biens/{bien}/interventions', [SuiviController::class, 'storeIntervention'])
            ->name('interventions.store');
        Route::post('/interventions/{intervention}/terminer', [SuiviController::class, 'terminerIntervention'])
            ->name('interventions.terminer');
        Route::post('/interventions/{intervention}/annuler', [SuiviController::class, 'annulerIntervention'])
            ->name('interventions.annuler');
    });

    Route::get('/inventaires', [InventaireController::class, 'index'])->name('inventaires.index');
    Route::get('/inventaires/nouveau', [InventaireController::class, 'create'])->name('inventaires.create');
    Route::post('/inventaires', [InventaireController::class, 'store'])->name('inventaires.store');
    Route::get('/inventaires/{inventaire}', [InventaireController::class, 'show'])->name('inventaires.show');
    Route::post('/inventaires/{inventaire}/lignes', [InventaireController::class, 'storeLigne'])
        ->name('inventaires.lignes.store');
    Route::post('/inventaires/{inventaire}/cloturer', [InventaireController::class, 'cloturer'])
        ->name('inventaires.cloturer');
});
