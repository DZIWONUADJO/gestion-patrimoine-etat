<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BienRequest;
use App\Models\Bien;
use App\Models\BienDocument;
use App\Models\Categorie;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BienController extends Controller
{
    public function index(Request $request): View
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

        return view('biens.index', [
            'biens' => $biens->paginate(15)->withQueryString(),
            'filters' => $filters,
            'etats' => Bien::ETATS,
            'categories' => Categorie::query()->where('is_active', true)->orderBy('libelle')->get(),
            'services' => Service::query()->where('is_active', true)->orderBy('nom_service')->get(),
        ]);
    }

    public function create(): View
    {
        return view('biens.form', [
            'bien' => new Bien,
            'categories' => Categorie::query()->where('is_active', true)->orderBy('libelle')->get(),
            'services' => Service::query()->where('is_active', true)->orderBy('nom_service')->get(),
            'etats' => Bien::ETATS,
        ]);
    }

    public function store(BienRequest $request): RedirectResponse
    {
        $bien = Bien::create($request->validated());

        return redirect()
            ->route('biens.show', $bien)
            ->with('success', 'Le bien a été enregistré.');
    }

    public function show(Bien $bien): View
    {
        $bien->load(['categorie', 'service', 'documents.uploadeur']);

        return view('biens.show', [
            'bien' => $bien,
        ]);
    }

    public function edit(Bien $bien): View|RedirectResponse
    {
        if (! $bien->is_active) {
            return redirect()->route('biens.show', $bien)
                ->with('error', 'Ce bien est archivé et ne peut plus être modifié.');
        }

        return view('biens.form', [
            'bien' => $bien,
            'categories' => Categorie::query()->where('is_active', true)->orderBy('libelle')->get(),
            'services' => Service::query()->where('is_active', true)->orderBy('nom_service')->get(),
            'etats' => Bien::ETATS,
        ]);
    }

    public function update(BienRequest $request, Bien $bien): RedirectResponse
    {
        $this->ensureNotArchived($bien);

        $bien->update($request->validated());

        return redirect()
            ->route('biens.show', $bien)
            ->with('success', 'Le bien a été mis à jour.');
    }

    public function archiver(Bien $bien): RedirectResponse
    {
        if ($bien->is_active) {
            $bien->update([
                'is_active' => false,
                'archive_le' => now(),
            ]);
        }

        return redirect()
            ->route('biens.show', $bien)
            ->with('success', 'Le bien a été archivé.');
    }

    public function restaurer(Bien $bien): RedirectResponse
    {
        $bien->update([
            'is_active' => true,
            'archive_le' => null,
        ]);

        return redirect()
            ->route('biens.show', $bien)
            ->with('success', 'Le bien a été restauré.');
    }

    public function storeDocument(Request $request, Bien $bien): RedirectResponse
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

        $bien->documents()->create([
            'nom' => $file->getClientOriginalName(),
            'chemin' => $path,
            'mime_type' => $file->getMimeType(),
            'taille' => $file->getSize(),
            'uploade_par' => $request->user()->getKey(),
        ]);

        return redirect()
            ->route('biens.show', $bien)
            ->with('success', 'Le justificatif a été ajouté.');
    }

    public function destroyDocument(Bien $bien, BienDocument $document): RedirectResponse
    {
        $this->ensureNotArchived($bien);

        if ($document->bien_id !== $bien->getKey()) {
            abort(404);
        }

        Storage::disk('local')->delete($document->chemin);
        $document->delete();

        return redirect()
            ->route('biens.show', $bien)
            ->with('success', 'Le justificatif a été supprimé.');
    }

    public function downloadDocument(Bien $bien, BienDocument $document): StreamedResponse
    {
        if ($document->bien_id !== $bien->getKey()) {
            abort(404);
        }

        return Storage::disk('local')->download($document->chemin, $document->nom);
    }

    private function ensureNotArchived(Bien $bien): void
    {
        if (! $bien->is_active) {
            throw ValidationException::withMessages([
                'bien' => ['Ce bien est archivé et ne peut plus être modifié.'],
            ]);
        }
    }
}
