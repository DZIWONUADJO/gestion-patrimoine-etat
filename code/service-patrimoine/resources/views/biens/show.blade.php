@extends('layouts.app')

@section('title', $bien->designation)
@section('header-label', 'Fiche bien')

@php
    $etatLabels = [
        'BON_ETAT' => 'Bon état',
        'ETAT_MOYEN' => 'État moyen',
        'A_REPARER' => 'À réparer',
        'EN_REPARATION' => 'En réparation',
        'HORS_SERVICE' => 'Hors service',
        'INTROUVABLE' => 'Introuvable',
    ];
    $etatStyles = [
        'BON_ETAT' => 'bg-emerald-50 text-emerald-700',
        'ETAT_MOYEN' => 'bg-amber-50 text-amber-700',
        'A_REPARER' => 'bg-orange-50 text-orange-700',
        'EN_REPARATION' => 'bg-sky-50 text-sky-700',
        'HORS_SERVICE' => 'bg-slate-100 text-slate-600',
        'INTROUVABLE' => 'bg-red-50 text-red-700',
    ];
@endphp

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
    @endif
    @error('bien')
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ $message }}</div>
    @enderror

    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
        <div>
            <a href="{{ route('biens.index') }}" class="text-xs font-semibold text-[#497e62] hover:underline">← Inventaire</a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-semibold tracking-tight text-slate-900">{{ $bien->designation }}</h1>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $etatStyles[$bien->etat] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ $etatLabels[$bien->etat] ?? $bien->etat }}
                </span>
                @unless ($bien->is_active)
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">Archivé le {{ $bien->archive_le?->format('d/m/Y') }}</span>
                @endunless
            </div>
            <p class="mt-1 font-mono text-sm text-slate-500">{{ $bien->numero_inventaire }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('manage-biens')
                @if ($bien->is_active)
                    <a href="{{ route('biens.edit', $bien) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Modifier</a>
                @endif
            @endcan
            @can('archive-biens')
                @if ($bien->is_active)
                    <form method="POST" action="{{ route('biens.archive', $bien) }}" onsubmit="return confirm('Archiver ce bien ? Il ne pourra plus être modifié.');">
                        @csrf
                        <button type="submit" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">Archiver</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('biens.restore', $bien) }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100">Restaurer</button>
                    </form>
                @endif
            @endcan
        </div>
    </section>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Caractéristiques</h2>
                <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Catégorie</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $bien->categorie?->libelle ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Service responsable</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $bien->service?->nom_service ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Marque</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $bien->marque ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Modèle</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $bien->modele ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">N° de série</dt>
                        <dd class="mt-1 font-mono text-sm text-slate-700">{{ $bien->numero_serie ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Localisation</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $bien->localisation ?: '—' }}</dd>
                    </div>
                </dl>
                @if ($bien->description)
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <dt class="text-xs font-semibold text-slate-400">Description</dt>
                        <dd class="mt-1 text-sm leading-6 text-slate-700">{{ $bien->description }}</dd>
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Justificatifs</h2>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $bien->documents->count() }}</span>
                </div>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($bien->documents as $document)
                        <div class="flex items-center justify-between gap-3 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-[#edf5ee] text-[#285c48]">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/>
                                        <path d="M14 3v6h6"/>
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <a href="{{ route('biens.documents.download', [$bien, $document]) }}" class="block truncate text-sm font-medium text-slate-800 hover:text-[#285c48] hover:underline">{{ $document->nom }}</a>
                                    <p class="text-xs text-slate-400">{{ $document->uploadeur?->name ?? '—' }} · {{ $document->created_at?->format('d/m/Y') }}
                                        @if ($document->taille) · {{ number_format($document->taille / 1024, 0, ',', ' ') }} Ko @endif
                                    </p>
                                </div>
                            </div>
                            @can('manage-biens')
                                @if ($bien->is_active)
                                    <form method="POST" action="{{ route('biens.documents.destroy', [$bien, $document]) }}" onsubmit="return confirm('Supprimer ce justificatif ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:border-red-200 hover:text-red-600">Supprimer</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500">Aucun justificatif pour l’instant.</p>
                    @endforelse
                </div>

                @can('manage-biens')
                    @if ($bien->is_active)
                        <form method="POST" action="{{ route('biens.documents.store', $bien) }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center">
                            @csrf
                            <input type="file" id="fichier" name="fichier" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv,.txt"
                                class="flex-1 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#edf5ee] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#285c48]">
                            <button type="submit" class="rounded-lg bg-[#103d32] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0d3227]">Ajouter</button>
                        </form>
                        @error('fichier')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                @endcan
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Acquisition</h2>
                <dl class="mt-4 space-y-3">
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Date</dt>
                        <dd class="mt-0.5 text-sm text-slate-700">{{ $bien->date_acquisition?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Valeur</dt>
                        <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ $bien->valeur_acquisition !== null ? number_format((float) $bien->valeur_acquisition, 0, ',', ' ').' FCFA' : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Fournisseur / source</dt>
                        <dd class="mt-0.5 text-sm text-slate-700">{{ $bien->fournisseur ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Historique</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Enregistré le</dt>
                        <dd class="text-slate-700">{{ $bien->created_at?->format('d/m/Y à H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400">Dernière modification</dt>
                        <dd class="text-slate-700">{{ $bien->updated_at?->format('d/m/Y à H:i') }}</dd>
                    </div>
                </dl>
                <p class="mt-4 border-t border-slate-100 pt-3 text-xs leading-5 text-slate-400">Les affectations, transferts et changements d’état historisés apparaîtront ici dans les prochains modules.</p>
            </section>
        </div>
    </div>
@endsection
