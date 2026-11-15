<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Bien;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RapportController extends Controller
{
    public function index(): View
    {
        return view('rapports.index');
    }

    public function biens(Request $request): mixed
    {
        $filters = $request->validate([
            'format' => ['sometimes', 'nullable', 'string', Rule::in(['pdf', 'csv'])],
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'categorie_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'service_id' => ['sometimes', 'nullable', 'integer', 'exists:services,id'],
            'etat' => ['sometimes', 'nullable', 'string', Rule::in(Bien::ETATS)],
            'is_active' => ['sometimes', 'nullable', Rule::in(['0', '1', 0, 1, true, false])],
        ]);

        $biens = $this->biensQuery($filters)
            ->with(['categorie:id,libelle', 'service:id,nom_service'])
            ->orderBy('numero_inventaire')
            ->limit(2000)
            ->get();

        $labels = $this->etatLabels();

        if (($filters['format'] ?? 'pdf') === 'csv') {
            return $this->csvResponse('inventaire_biens', [
                ['N° inventaire', 'Désignation', 'Catégorie', 'Service', 'État', 'Localisation', 'Marque', 'Modèle', 'N° série', 'Date acquisition', 'Valeur (FCFA)', 'Statut'],
                ...$biens->map(fn (Bien $b) => [
                    $b->numero_inventaire,
                    $b->designation,
                    $b->categorie?->libelle,
                    $b->service?->nom_service,
                    $labels[$b->etat] ?? $b->etat,
                    $b->localisation,
                    $b->marque,
                    $b->modele,
                    $b->numero_serie,
                    $b->date_acquisition?->format('d/m/Y'),
                    $b->valeur_acquisition !== null ? (float) $b->valeur_acquisition : null,
                    $b->is_active ? 'Actif' : 'Archivé',
                ]),
            ]);
        }

        $pdf = Pdf::loadView('rapports.biens', [
            'biens' => $biens,
            'labels' => $labels,
            'filtres' => $filters,
            'genereLe' => now()->format('d/m/Y à H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('inventaire_biens.pdf');
    }

    public function mouvements(Request $request): mixed
    {
        $filters = $request->validate([
            'format' => ['sometimes', 'nullable', 'string', Rule::in(['pdf', 'csv'])],
            'statut' => ['sometimes', 'nullable', 'string', Rule::in(array_keys($this->statutLabels()))],
            'bien_id' => ['sometimes', 'nullable', 'integer', 'exists:biens,id'],
        ]);

        $affectations = Affectation::query()
            ->with([
                'bien:id,numero_inventaire,designation',
                'serviceOrigine:id,nom_service',
                'serviceDestinataire:id,nom_service',
                'demandeur:id,name',
                'valideur:id,name',
            ])
            ->when($filters['statut'] ?? null, fn (Builder $q, $s) => $q->where('statut', $s))
            ->when($filters['bien_id'] ?? null, fn (Builder $q, $id) => $q->where('bien_id', $id))
            ->orderByDesc('date_demande')
            ->limit(2000)
            ->get();

        $labels = $this->statutLabels();

        if (($filters['format'] ?? 'pdf') === 'csv') {
            return $this->csvResponse('mouvements_affectations', [
                ['Bien', 'Service origine', 'Service destinataire', 'Motif', 'Statut', 'Demandé par', 'Demandé le', 'Décision le', 'Validé par', 'Motif décision'],
                ...$affectations->map(fn (Affectation $a) => [
                    ($a->bien?->numero_inventaire ?? '').' — '.($a->bien?->designation ?? ''),
                    $a->serviceOrigine?->nom_service,
                    $a->serviceDestinataire?->nom_service,
                    $a->motif,
                    $labels[$a->statut] ?? $a->statut,
                    $a->demandeur?->name,
                    $a->date_demande?->format('d/m/Y'),
                    $a->date_validation?->format('d/m/Y'),
                    $a->valideur?->name,
                    $a->motif_decision,
                ]),
            ]);
        }

        $pdf = Pdf::loadView('rapports.mouvements', [
            'affectations' => $affectations,
            'labels' => $labels,
            'genereLe' => now()->format('d/m/Y à H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('mouvements_affectations.pdf');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Bien>
     */
    private function biensQuery(array $filters): Builder
    {
        $query = Bien::query();

        if (! empty($filters['q'])) {
            $search = '%'.$filters['q'].'%';
            $query->where(function ($q) use ($search) {
                $q->where('numero_inventaire', 'like', $search)
                    ->orWhere('designation', 'like', $search)
                    ->orWhere('numero_serie', 'like', $search);
            });
        }

        foreach (['categorie_id', 'service_id', 'etat', 'is_active'] as $field) {
            if (array_key_exists($field, $filters) && $filters[$field] !== null && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        return $query;
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function csvResponse(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<string, string>
     */
    private function etatLabels(): array
    {
        return [
            'BON_ETAT' => 'Bon état',
            'ETAT_MOYEN' => 'État moyen',
            'A_REPARER' => 'À réparer',
            'EN_REPARATION' => 'En réparation',
            'HORS_SERVICE' => 'Hors service',
            'INTROUVABLE' => 'Introuvable',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function statutLabels(): array
    {
        return [
            'EN_ATTENTE' => 'En attente',
            'VALIDEE' => 'Validée',
            'REJETEE' => 'Rejetée',
            'ANNULEE' => 'Annulée',
        ];
    }
}
