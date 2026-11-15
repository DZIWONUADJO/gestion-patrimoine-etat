<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AffectationRequest;
use App\Models\Affectation;
use App\Models\Bien;
use App\Services\AffectationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AffectationController extends Controller
{
    public function __construct(private readonly AffectationService $affectations) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'bien_id' => ['sometimes', 'nullable', 'integer', 'exists:biens,id'],
            'statut' => ['sometimes', 'nullable', 'string', Rule::in(Affectation::STATUTS)],
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $affectations = Affectation::query()
            ->with([
                'bien:id,numero_inventaire,designation',
                'serviceOrigine:id,nom_service',
                'serviceDestinataire:id,nom_service',
                'demandeur:id,name',
                'valideur:id,name',
            ])
            ->orderByDesc('date_demande');

        if (! empty($filters['bien_id'])) {
            $affectations->where('bien_id', $filters['bien_id']);
        }

        if (! empty($filters['statut'])) {
            $affectations->where('statut', $filters['statut']);
        }

        if (! empty($filters['q'])) {
            $search = '%'.$filters['q'].'%';
            $affectations->whereHas('bien', function ($query) use ($search) {
                $query->where('numero_inventaire', 'like', $search)
                    ->orWhere('designation', 'like', $search);
            });
        }

        return view('mouvements.index', [
            'affectations' => $affectations->paginate(15)->withQueryString(),
            'filters' => $filters,
            'statuts' => Affectation::STATUTS,
            'biens' => Bien::query()->orderBy('numero_inventaire')->get(['id', 'numero_inventaire', 'designation']),
        ]);
    }

    public function store(AffectationRequest $request, Bien $bien): RedirectResponse
    {
        $this->affectations->proposer($bien, $request->validated(), $request->user());

        return redirect()
            ->route('biens.show', $bien)
            ->with('success', 'La demande d’affectation a été enregistrée et attend une validation.');
    }

    public function valider(Request $request, Affectation $affectation): RedirectResponse
    {
        $validated = $request->validate([
            'motif_decision' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $this->affectations->valider(
            $affectation,
            $request->user(),
            $validated['motif_decision'] ?? null,
        );

        return redirect()
            ->route('mouvements.index')
            ->with('success', 'La demande a été validée.');
    }

    public function rejeter(Request $request, Affectation $affectation): RedirectResponse
    {
        $validated = $request->validate([
            'motif_decision' => ['required', 'string', 'max:255'],
        ]);

        $this->affectations->rejeter(
            $affectation,
            $request->user(),
            $validated['motif_decision'],
        );

        return redirect()
            ->route('mouvements.index')
            ->with('success', 'La demande a été rejetée.');
    }

    public function annuler(Request $request, Affectation $affectation): RedirectResponse
    {
        $this->affectations->annuler($affectation, $request->user());

        return redirect()
            ->route('mouvements.index')
            ->with('success', 'La demande a été annulée.');
    }
}
