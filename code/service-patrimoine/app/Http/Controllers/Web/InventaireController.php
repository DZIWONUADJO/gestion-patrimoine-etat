<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Inventaire;
use App\Services\SuiviService;
use Illuminate\Http\Request;

class InventaireController extends Controller
{
    public function __construct(private readonly SuiviService $suivi) {}

    public function index()
    {
        $inventaires = Inventaire::query()
            ->with(['agentResponsable:id,name'])
            ->withCount('lignes')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('inventaires.index', [
            'inventaires' => $inventaires,
        ]);
    }

    public function create()
    {
        return view('inventaires.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'lieu' => ['sometimes', 'nullable', 'string', 'max:255'],
            'date' => ['sometimes', 'nullable', 'date'],
        ]);

        Inventaire::create([
            'libelle' => $validated['libelle'],
            'lieu' => $validated['lieu'] ?? null,
            'date' => $validated['date'] ?? now()->toDateString(),
            'agent_responsable_id' => $request->user()->getKey(),
            'cree_par' => $request->user()->getKey(),
        ]);

        return redirect()->route('inventaires.index')->with('success', 'Inventaire créé.');
    }

    public function show(Inventaire $inventaire)
    {
        $inventaire->load([
            'agentResponsable:id,name',
            'createur:id,name',
            'lignes' => fn ($q) => $q->with([
                'bien:id,numero_inventaire,designation,service_id,etat',
                'bien.service:id,nom_service',
                'releveur:id,name',
            ])->orderBy('id'),
        ]);

        $biens = Bien::query()
            ->where('is_active', true)
            ->with('service:id,nom_service')
            ->orderBy('numero_inventaire')
            ->get(['id', 'numero_inventaire', 'designation', 'service_id', 'etat']);

        return view('inventaires.show', [
            'inventaire' => $inventaire,
            'biens' => $biens,
        ]);
    }

    public function storeLigne(Request $request, Inventaire $inventaire)
    {
        $validated = $request->validate([
            'bien_id' => ['required', 'integer', 'exists:biens,id'],
            'statut' => ['required', 'string', 'in:PRESENT,INTROUVABLE,ANOMALIE'],
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $bien = Bien::findOrFail($validated['bien_id']);

        $this->suivi->releverLigne($inventaire, $bien, $validated, $request->user());

        return back()->with('success', 'Bien relevé dans l’inventaire.');
    }

    public function cloturer(Request $request, Inventaire $inventaire)
    {
        $user = $request->user();
        $isAgent = $inventaire->agent_responsable_id === $user->getKey();

        if (! $isAgent && $user->role !== 'ADMIN') {
            abort(403, 'Seul l’agent responsable ou un administrateur peut clôturer cet inventaire.');
        }

        $this->suivi->cloturerInventaire($inventaire);

        return back()->with('success', 'Inventaire clôturé.');
    }
}
