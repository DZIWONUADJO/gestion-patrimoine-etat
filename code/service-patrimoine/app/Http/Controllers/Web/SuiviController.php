<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Intervention;
use App\Services\SuiviService;
use Illuminate\Http\Request;

class SuiviController extends Controller
{
    public function __construct(private readonly SuiviService $suivi) {}

    public function changerEtat(Request $request, Bien $bien)
    {
        $validated = $request->validate([
            'etat' => ['required', 'string', 'in:'.implode(',', Bien::ETATS)],
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $this->suivi->changerEtat(
            $bien,
            $validated['etat'],
            $validated['commentaire'] ?? null,
            $request->user(),
        );

        return back()->with('success', 'État du bien mis à jour.');
    }

    public function storeIntervention(Request $request, Bien $bien)
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'required', 'string', 'in:REPARATION,MAINTENANCE,VISITE_TECHNIQUE'],
            'description' => ['required', 'string', 'max:2000'],
            'prestataire' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cout' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'date_debut' => ['required', 'date'],
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $this->suivi->ouvrirIntervention($bien, $validated, $request->user());

        return back()->with('success', 'Intervention enregistrée.');
    }

    public function terminerIntervention(Request $request, Intervention $intervention)
    {
        $validated = $request->validate([
            'etat' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', Bien::ETATS)],
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $this->suivi->cloturerIntervention($intervention, $request->user(), $validated);

        return back()->with('success', 'Intervention clôturée.');
    }

    public function annulerIntervention(Intervention $intervention)
    {
        $this->suivi->annulerIntervention($intervention, auth()->user());

        return back()->with('success', 'Intervention annulée.');
    }
}
