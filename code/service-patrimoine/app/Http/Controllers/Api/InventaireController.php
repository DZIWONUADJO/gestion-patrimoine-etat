<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Inventaire;
use App\Models\InventaireLigne;
use App\Services\SuiviService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventaireController extends Controller
{
    public function __construct(private readonly SuiviService $suivi) {}

    public function index(Request $request): JsonResponse
    {
        $inventaires = Inventaire::query()
            ->with(['agentResponsable:id,name', 'createur:id,name'])
            ->withCount('lignes')
            ->orderByDesc('date')
            ->paginate(50);

        return response()->json(['data' => $inventaires]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'lieu' => ['sometimes', 'nullable', 'string', 'max:255'],
            'date' => ['sometimes', 'nullable', 'date'],
            'agent_responsable_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ]);

        $inventaire = Inventaire::create([
            'libelle' => $validated['libelle'],
            'lieu' => $validated['lieu'] ?? null,
            'date' => $validated['date'] ?? now()->toDateString(),
            'agent_responsable_id' => $validated['agent_responsable_id'] ?? $request->user()->getKey(),
            'cree_par' => $request->user()->getKey(),
        ]);

        return response()->json([
            'data' => $this->inventaireData($inventaire->load(['agentResponsable:id,name', 'createur:id,name'])),
        ], 201);
    }

    public function show(Inventaire $inventaire): JsonResponse
    {
        $inventaire->load([
            'agentResponsable:id,name',
            'createur:id,name',
            'lignes' => fn ($q) => $q->with([
                'bien:id,numero_inventaire,designation,service_id',
                'bien.service:id,nom_service',
                'releveur:id,name',
            ])->orderBy('id'),
        ]);

        return response()->json([
            'data' => $this->inventaireData($inventaire, true),
        ]);
    }

    public function storeLigne(Request $request, Inventaire $inventaire): JsonResponse
    {
        $validated = $request->validate([
            'bien_id' => ['sometimes', 'nullable', 'integer', 'exists:biens,id', 'required_without:numero_inventaire'],
            'numero_inventaire' => ['sometimes', 'nullable', 'string', 'max:100', 'required_without:bien_id'],
            'statut' => ['required', 'string', Rule::in(InventaireLigne::STATUTS)],
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $bien = isset($validated['bien_id'])
            ? Bien::findOrFail($validated['bien_id'])
            : Bien::where('numero_inventaire', $validated['numero_inventaire'])->firstOrFail();

        $ligne = $this->suivi->releverLigne($inventaire, $bien, $validated, $request->user());

        return response()->json([
            'data' => [
                'id' => $ligne->getKey(),
                'bien_id' => $ligne->bien_id,
                'statut' => $ligne->statut,
                'commentaire' => $ligne->commentaire,
            ],
        ], $ligne->wasRecentlyCreated ? 201 : 200);
    }

    public function cloturer(Request $request, Inventaire $inventaire): JsonResponse
    {
        $user = $request->user();
        $isAgent = $inventaire->agent_responsable_id === $user->getKey();

        if (! $isAgent && $user->role !== 'ADMIN') {
            return response()->json([
                'message' => 'Seul l’agent responsable ou un administrateur peut clôturer cet inventaire.',
            ], 403);
        }

        $inventaire = $this->suivi->cloturerInventaire($inventaire);

        return response()->json([
            'data' => $this->inventaireData($inventaire->load(['agentResponsable:id,name', 'createur:id,name'])),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function inventaireData(Inventaire $inventaire, bool $avecLignes = false): array
    {
        $data = [
            'id' => $inventaire->getKey(),
            'libelle' => $inventaire->libelle,
            'lieu' => $inventaire->lieu,
            'date' => $inventaire->date?->toDateString(),
            'statut' => $inventaire->statut,
            'agent_responsable' => $inventaire->relationLoaded('agentResponsable') && $inventaire->agentResponsable
                ? ['id' => $inventaire->agentResponsable->id, 'name' => $inventaire->agentResponsable->name] : null,
            'cree_par' => $inventaire->relationLoaded('createur') && $inventaire->createur
                ? ['id' => $inventaire->createur->id, 'name' => $inventaire->createur->name] : null,
            'lignes_count' => $inventaire->relationLoaded('lignes')
                ? $inventaire->lignes->count()
                : $inventaire->lignes()->count(),
        ];

        if ($avecLignes) {
            $data['lignes'] = $inventaire->lignes->map(fn (InventaireLigne $l) => [
                'id' => $l->getKey(),
                'bien' => $l->bien ? [
                    'id' => $l->bien->id,
                    'numero_inventaire' => $l->bien->numero_inventaire,
                    'designation' => $l->bien->designation,
                    'service' => $l->bien->relationLoaded('service') && $l->bien->service
                        ? ['id' => $l->bien->service->id, 'nom_service' => $l->bien->service->nom_service] : null,
                ] : null,
                'statut' => $l->statut,
                'commentaire' => $l->commentaire,
                'releve_par' => $l->relationLoaded('releveur') && $l->releveur
                    ? ['id' => $l->releveur->id, 'name' => $l->releveur->name] : null,
                'created_at' => $l->created_at?->toIso8601String(),
            ])->values();
        }

        return $data;
    }
}
