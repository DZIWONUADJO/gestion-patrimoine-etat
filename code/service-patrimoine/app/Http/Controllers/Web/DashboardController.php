<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Categorie;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

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
        ]);
    }
}
