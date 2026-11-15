<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Intervention;
use App\Services\SuiviService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuiviController extends Controller
{
    public function __construct(private readonly SuiviService $suivi) {}

    public function indexHistoriques(Bien $bien): JsonResponse
    {
        return response()->json([
            'data' => $bien->etatHistoriques()
                ->with('auteur:id,name')
                ->limit(100)
                ->get()
                ->map(fn ($h) => [
                    'id' => $h->getKey(),
                    'de_etat' => $h->de_etat,
                    'vers_etat' => $h->vers_etat,
                    'commentaire' => $h->commentaire,
                    'auteur' => $h->auteur ? ['id' => $h->auteur->id, 'name' => $h->auteur->name] : null,
                    'created_at' => $h->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function changerEtat(Request $request, Bien $bien): JsonResponse
    {
        $validated = $request->validate([
            'etat' => ['required', 'string', Rule::in(Bien::ETATS)],
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $historique = $this->suivi->changerEtat(
            $bien,
            $validated['etat'],
            $validated['commentaire'] ?? null,
            $request->user(),
        );

        return response()->json([
            'data' => [
                'etat' => $bien->refresh()->etat,
                'historique' => [
                    'id' => $historique->getKey(),
                    'de_etat' => $historique->de_etat,
                    'vers_etat' => $historique->vers_etat,
                    'commentaire' => $historique->commentaire,
                    'created_at' => $historique->created_at?->toIso8601String(),
                ],
            ],
        ], 201);
    }

    public function indexInterventions(Bien $bien): JsonResponse
    {
        return response()->json([
            'data' => $bien->interventions()
                ->with('auteur:id,name')
                ->get()
                ->map(fn (Intervention $i) => $this->interventionData($i)),
        ]);
    }

    public function storeIntervention(Request $request, Bien $bien): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'required', 'string', Rule::in(['REPARATION', 'MAINTENANCE', 'VISITE_TECHNIQUE'])],
            'description' => ['required', 'string', 'max:2000'],
            'prestataire' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cout' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'date_debut' => ['required', 'date'],
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $intervention = $this->suivi->ouvrirIntervention($bien, $validated, $request->user());

        return response()->json([
            'data' => $this->interventionData($intervention->load('auteur:id,name')),
        ], 201);
    }

    public function terminerIntervention(Request $request, Intervention $intervention): JsonResponse
    {
        $validated = $request->validate([
            'etat' => ['sometimes', 'nullable', 'string', Rule::in(Bien::ETATS)],
            'date_fin' => ['sometimes', 'nullable', 'date'],
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $intervention = $this->suivi->cloturerIntervention($intervention, $request->user(), $validated);

        return response()->json([
            'data' => $this->interventionData($intervention->load(['auteur:id,name', 'bien:id,numero_inventaire,designation'])),
        ]);
    }

    public function annulerIntervention(Request $request, Intervention $intervention): JsonResponse
    {
        $intervention = $this->suivi->annulerIntervention($intervention, $request->user());

        return response()->json([
            'data' => $this->interventionData($intervention->load('auteur:id,name')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function interventionData(Intervention $i): array
    {
        return [
            'id' => $i->getKey(),
            'bien_id' => $i->bien_id,
            'type' => $i->type,
            'description' => $i->description,
            'prestataire' => $i->prestataire,
            'cout' => $i->cout,
            'etat_precedent' => $i->etat_precedent,
            'statut' => $i->statut,
            'date_debut' => $i->date_debut?->toDateString(),
            'date_fin' => $i->date_fin?->toDateString(),
            'commentaire' => $i->commentaire,
            'auteur' => $i->relationLoaded('auteur') && $i->auteur
                ? ['id' => $i->auteur->id, 'name' => $i->auteur->name] : null,
        ];
    }
}
