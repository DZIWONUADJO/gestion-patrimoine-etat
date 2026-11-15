<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Bien;
use App\Models\Categorie;
use App\Models\Intervention;
use App\Models\Inventaire;
use App\Models\InventaireLigne;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $biensParEtat = Bien::query()
            ->where('is_active', true)
            ->select('etat', DB::raw('count(*) as total'))
            ->groupBy('etat')
            ->pluck('total', 'etat');

        return view('dashboard', [
            'user' => $user,
            'activeCategoryCount' => Categorie::query()->where('is_active', true)->count(),
            'activeServiceCount' => Service::query()->where('is_active', true)->count(),
            'assetCount' => $user->role === 'ADMIN' ? Bien::query()->count() : null,
            'categories' => Categorie::query()
                ->where('is_active', true)
                ->orderBy('libelle')
                ->limit(5)
                ->get(),
            'services' => Service::query()
                ->where('is_active', true)
                ->with('parent')
                ->orderBy('nom_service')
                ->limit(5)
                ->get(),
            'biensParEtat' => $biensParEtat,
            'affectationsEnAttente' => Affectation::query()->where('statut', 'EN_ATTENTE')->count(),
            'interventionsEnCours' => Intervention::query()->where('statut', 'EN_COURS')->count(),
            'inventairesEnCours' => Inventaire::query()->where('statut', 'EN_COURS')->count(),
            'anomaliesRecentes' => InventaireLigne::query()
                ->whereIn('statut', ['ANOMALIE', 'INTROUVABLE'])
                ->with(['bien:id,numero_inventaire,designation', 'inventaire:id,libelle', 'releveur:id,name'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
            'derniersMouvements' => Affectation::query()
                ->with(['bien:id,numero_inventaire,designation', 'serviceDestinataire:id,nom_service', 'demandeur:id,name'])
                ->orderByDesc('date_demande')
                ->limit(5)
                ->get(),
        ]);
    }
}
