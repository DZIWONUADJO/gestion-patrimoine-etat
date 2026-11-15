<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AffectationRequest;
use App\Models\Affectation;
use App\Models\Bien;
use App\Models\Service;
use App\Services\AffectationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AffectationController extends Controller
{
    public function __construct(private readonly AffectationService $affectations) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'bien_id' => ['sometimes', 'nullable', 'integer', 'exists:biens,id'],
            'statut' => ['sometimes', 'nullable', 'string', Rule::in(Affectation::STATUTS)],
            'en_attente' => ['sometimes', 'boolean'],
        ]);

        $affectations = Affectation::query()
            ->with($this->relations())
            ->orderByDesc('date_demande');

        if (! empty($filters['bien_id'])) {
            $affectations->where('bien_id', $filters['bien_id']);
        }

        if (! empty($filters['statut'])) {
            $affectations->where('statut', $filters['statut']);
        }

        if (array_key_exists('en_attente', $filters) && $filters['en_attente']) {
            $affectations->where('statut', 'EN_ATTENTE');
        }

        return response()->json([
            'data' => $affectations->paginate(50),
        ]);
    }

    public function store(AffectationRequest $request, Bien $bien): JsonResponse
    {
        $affectation = $this->affectations->proposer(
            $bien,
            $request->validated(),
            $request->user(),
        );

        return response()->json([
            'data' => $this->affectationData($affectation->load($this->relations())),
        ], 201);
    }

    public function show(Affectation $affectation): JsonResponse
    {
        return response()->json([
            'data' => $this->affectationData($affectation->load($this->relations())),
        ]);
    }

    public function valider(Request $request, Affectation $affectation): JsonResponse
    {
        $validated = $request->validate([
            'motif_decision' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $affectation = $this->affectations->valider(
            $affectation,
            $request->user(),
            $validated['motif_decision'] ?? null,
        );

        return response()->json([
            'data' => $this->affectationData($affectation->load($this->relations())),
        ]);
    }

    public function rejeter(Request $request, Affectation $affectation): JsonResponse
    {
        $validated = $request->validate([
            'motif_decision' => ['required', 'string', 'max:255'],
        ]);

        $affectation = $this->affectations->rejeter(
            $affectation,
            $request->user(),
            $validated['motif_decision'],
        );

        return response()->json([
            'data' => $this->affectationData($affectation->load($this->relations())),
        ]);
    }

    public function annuler(Request $request, Affectation $affectation): JsonResponse
    {
        $affectation = $this->affectations->annuler($affectation, $request->user());

        return response()->json([
            'data' => $this->affectationData($affectation->load($this->relations())),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'bien:id,numero_inventaire,designation',
            'serviceOrigine:id,nom_service',
            'serviceDestinataire:id,nom_service',
            'demandeur:id,name',
            'valideur:id,name',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function affectationData(Affectation $affectation): array
    {
        return [
            'id' => $affectation->getKey(),
            'bien_id' => $affectation->bien_id,
            'bien' => $affectation->relationLoaded('bien') && $affectation->bien ? [
                'id' => $affectation->bien->getKey(),
                'numero_inventaire' => $affectation->bien->numero_inventaire,
                'designation' => $affectation->bien->designation,
            ] : null,
            'service_origine_id' => $affectation->service_origine_id,
            'service_origine' => $this->serviceData($affectation->serviceOrigine),
            'service_destinataire_id' => $affectation->service_destinataire_id,
            'service_destinataire' => $this->serviceData($affectation->serviceDestinataire),
            'localisation_origine' => $affectation->localisation_origine,
            'localisation_destinataire' => $affectation->localisation_destinataire,
            'motif' => $affectation->motif,
            'statut' => $affectation->statut,
            'date_effet' => $affectation->date_effet?->toDateString(),
            'date_fin' => $affectation->date_fin?->toDateString(),
            'date_demande' => $affectation->date_demande?->toIso8601String(),
            'date_validation' => $affectation->date_validation?->toIso8601String(),
            'demandeur' => $affectation->relationLoaded('demandeur') && $affectation->demandeur ? [
                'id' => $affectation->demandeur->getKey(),
                'name' => $affectation->demandeur->name,
            ] : null,
            'valideur' => $affectation->relationLoaded('valideur') && $affectation->valideur ? [
                'id' => $affectation->valideur->getKey(),
                'name' => $affectation->valideur->name,
            ] : null,
            'motif_decision' => $affectation->motif_decision,
        ];
    }

    /**
     * @return array{id: int|string, nom_service: string|null}|null
     */
    private function serviceData(?Service $service): ?array
    {
        if (! $service) {
            return null;
        }

        return [
            'id' => $service->getKey(),
            'nom_service' => $service->nom_service,
        ];
    }
}
