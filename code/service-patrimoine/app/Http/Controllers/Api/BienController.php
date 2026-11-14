<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\BienDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BienController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'categorie_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'service_id' => ['sometimes', 'nullable', 'integer', 'exists:services,id'],
            'etat' => ['sometimes', 'nullable', 'string', Rule::in(Bien::ETATS)],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $biens = Bien::query()
            ->with(['categorie:id,libelle', 'service:id,nom_service'])
            ->orderBy('numero_inventaire');

        if (! empty($filters['q'])) {
            $search = '%'.$filters['q'].'%';
            $biens->where(function ($query) use ($search) {
                $query->where('numero_inventaire', 'like', $search)
                    ->orWhere('designation', 'like', $search)
                    ->orWhere('numero_serie', 'like', $search);
            });
        }

        foreach (['categorie_id', 'service_id', 'etat', 'is_active'] as $field) {
            if (array_key_exists($field, $filters) && $filters[$field] !== null) {
                $biens->where($field, $filters[$field]);
            }
        }

        return response()->json([
            'data' => $biens->paginate(50),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateBien($request);

        $bien = Bien::create($validated);

        return response()->json([
            'data' => $this->bienData($bien->load(['categorie:id,libelle', 'service:id,nom_service'])),
        ], 201);
    }

    public function show(Bien $bien): JsonResponse
    {
        $bien->load(['categorie:id,libelle', 'service:id,nom_service', 'documents']);

        return response()->json([
            'data' => $this->bienData($bien),
        ]);
    }

    public function update(Request $request, Bien $bien): JsonResponse
    {
        $this->ensureNotArchived($bien);

        $validated = $this->validateBien($request, $bien);

        $bien->update($validated);

        return response()->json([
            'data' => $this->bienData($bien->refresh()->load(['categorie:id,libelle', 'service:id,nom_service'])),
        ]);
    }

    public function archiver(Bien $bien): JsonResponse
    {
        if (! $bien->is_active) {
            return response()->json([
                'data' => $this->bienData($bien),
            ]);
        }

        $bien->update([
            'is_active' => false,
            'archive_le' => now(),
        ]);

        return response()->json([
            'data' => $this->bienData($bien->refresh()),
        ]);
    }

    public function restaurer(Bien $bien): JsonResponse
    {
        $bien->update([
            'is_active' => true,
            'archive_le' => null,
        ]);

        return response()->json([
            'data' => $this->bienData($bien->refresh()),
        ]);
    }

    public function storeDocument(Request $request, Bien $bien): JsonResponse
    {
        $this->ensureNotArchived($bien);

        $validated = $request->validate([
            'fichier' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv,txt',
            ],
        ]);

        $file = $validated['fichier'];
        $path = $file->store('biens/'.$bien->getKey());

        $document = $bien->documents()->create([
            'nom' => $file->getClientOriginalName(),
            'chemin' => $path,
            'mime_type' => $file->getMimeType(),
            'taille' => $file->getSize(),
            'uploade_par' => $request->user()->getKey(),
        ]);

        return response()->json([
            'data' => $this->documentData($document),
        ], 201);
    }

    public function destroyDocument(Bien $bien, BienDocument $document): JsonResponse
    {
        $this->ensureNotArchived($bien);

        if ($document->bien_id !== $bien->getKey()) {
            abort(404);
        }

        Storage::disk('local')->delete($document->chemin);
        $document->delete();

        return response()->json(null, 204);
    }

    public function downloadDocument(Bien $bien, BienDocument $document): StreamedResponse
    {
        if ($document->bien_id !== $bien->getKey()) {
            abort(404);
        }

        return Storage::disk('local')->download($document->chemin, $document->nom);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBien(Request $request, ?Bien $bien = null): array
    {
        $required = $bien === null ? 'required' : 'sometimes';

        return $request->validate([
            'numero_inventaire' => [
                $required, 'string', 'max:100',
                Rule::unique('biens', 'numero_inventaire')->ignore($bien?->getKey()),
            ],
            'designation' => [$required, 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'categorie_id' => [$required, 'integer', 'exists:categories,id'],
            'service_id' => [$required, 'integer', 'exists:services,id'],
            'etat' => ['sometimes', 'required', 'string', Rule::in(Bien::ETATS)],
            'localisation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'marque' => ['sometimes', 'nullable', 'string', 'max:100'],
            'modele' => ['sometimes', 'nullable', 'string', 'max:100'],
            'numero_serie' => ['sometimes', 'nullable', 'string', 'max:100'],
            'date_acquisition' => ['sometimes', 'nullable', 'date'],
            'valeur_acquisition' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'fournisseur' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);
    }

    private function ensureNotArchived(Bien $bien): void
    {
        if (! $bien->is_active) {
            throw ValidationException::withMessages([
                'bien' => ['Ce bien est archivé et ne peut plus être modifié.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function bienData(Bien $bien): array
    {
        $data = [
            'id' => $bien->getKey(),
            'numero_inventaire' => $bien->numero_inventaire,
            'designation' => $bien->designation,
            'description' => $bien->description,
            'categorie_id' => $bien->categorie_id,
            'service_id' => $bien->service_id,
            'etat' => $bien->etat,
            'localisation' => $bien->localisation,
            'marque' => $bien->marque,
            'modele' => $bien->modele,
            'numero_serie' => $bien->numero_serie,
            'date_acquisition' => $bien->date_acquisition?->toDateString(),
            'valeur_acquisition' => $bien->valeur_acquisition,
            'fournisseur' => $bien->fournisseur,
            'is_active' => $bien->is_active,
            'archive_le' => $bien->archive_le?->toIso8601String(),
        ];

        if ($bien->relationLoaded('categorie')) {
            $data['categorie'] = $bien->categorie ? [
                'id' => $bien->categorie->getKey(),
                'libelle' => $bien->categorie->libelle,
            ] : null;
        }

        if ($bien->relationLoaded('service')) {
            $data['service'] = $bien->service ? [
                'id' => $bien->service->getKey(),
                'nom_service' => $bien->service->nom_service,
            ] : null;
        }

        if ($bien->relationLoaded('documents')) {
            $data['documents'] = $bien->documents
                ->map(fn (BienDocument $document) => $this->documentData($document))
                ->values();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function documentData(BienDocument $document): array
    {
        return [
            'id' => $document->getKey(),
            'nom' => $document->nom,
            'mime_type' => $document->mime_type,
            'taille' => $document->taille,
            'created_at' => $document->created_at?->toIso8601String(),
        ];
    }
}
