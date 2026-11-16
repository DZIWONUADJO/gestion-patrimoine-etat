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
            <a href="{{ route('biens.index') }}" class="text-xs font-semibold text-[#006a4f] hover:underline">← Inventaire</a>
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
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-[#eef4ef] text-[#006a4f]">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/>
                                        <path d="M14 3v6h6"/>
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <a href="{{ route('biens.documents.download', [$bien, $document]) }}" class="block truncate text-sm font-medium text-slate-800 hover:text-[#006a4f] hover:underline">{{ $document->nom }}</a>
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
                                class="flex-1 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef4ef] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#006a4f]">
                            <button type="submit" class="rounded-lg bg-[#006a4f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#004d3a]">Ajouter</button>
                        </form>
                        @error('fichier')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                @endcan
            </section>
        </div>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Affectations et transferts</h2>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $bien->affectations->count() }}</span>
                </div>

                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($bien->affectations as $affectation)
                        <div class="py-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-medium text-slate-800">
                                    {{ $affectation->serviceOrigine?->nom_service ?? 'Non affecté' }}
                                    <span class="text-slate-400">→</span>
                                    {{ $affectation->serviceDestinataire?->nom_service ?? '—' }}
                                </p>
                                @php
                                    $affectationStyles = [
                                        'EN_ATTENTE' => 'bg-amber-50 text-amber-700',
                                        'VALIDEE' => 'bg-emerald-50 text-emerald-700',
                                        'REJETEE' => 'bg-red-50 text-red-700',
                                        'ANNULEE' => 'bg-slate-100 text-slate-500',
                                    ];
                                    $affectationLabels = [
                                        'EN_ATTENTE' => 'En attente',
                                        'VALIDEE' => 'Validée',
                                        'REJETEE' => 'Rejetée',
                                        'ANNULEE' => 'Annulée',
                                    ];
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $affectationStyles[$affectation->statut] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $affectationLabels[$affectation->statut] ?? $affectation->statut }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                Demandée par {{ $affectation->demandeur?->name ?? '—' }} le {{ $affectation->date_demande?->format('d/m/Y') }}
                                @if ($affectation->date_effet) · effet le {{ $affectation->date_effet->format('d/m/Y') }} @endif
                                @if ($affectation->date_fin) · jusqu’au {{ $affectation->date_fin->format('d/m/Y') }} @endif
                                @if ($affectation->valideur) · validée par {{ $affectation->valideur->name }} @endif
                            </p>
                            @if ($affectation->motif)
                                <p class="mt-1 text-xs text-slate-400">Motif : {{ $affectation->motif }}</p>
                            @endif
                            @if ($affectation->motif_decision && $affectation->statut !== 'EN_ATTENTE')
                                <p class="mt-1 text-xs text-slate-400">Décision : {{ $affectation->motif_decision }}</p>
                            @endif
                            @if ($affectation->localisation_destinataire)
                                <p class="mt-1 text-xs text-slate-400">Nouvelle localisation : {{ $affectation->localisation_destinataire }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500">Aucune affectation enregistrée.</p>
                    @endforelse
                </div>

                @can('manage-biens')
                    @if ($bien->is_active)
                        <form method="POST" action="{{ route('affectations.store', $bien) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-2">
                            @csrf
                            <div class="sm:col-span-2">
                                <label for="service_destinataire_id" class="mb-1 block text-xs font-semibold text-slate-600">Service destinataire</label>
                                <select id="service_destinataire_id" name="service_destinataire_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f] @error('service_destinataire_id') border-red-300 @enderror">
                                    <option value="">Choisir…</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" @selected((string) old('service_destinataire_id') === (string) $service->id)>{{ $service->nom_service }}</option>
                                    @endforeach
                                </select>
                                @error('service_destinataire_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                @error('bien')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="localisation_destinataire" class="mb-1 block text-xs font-semibold text-slate-600">Nouvelle localisation</label>
                                <input type="text" id="localisation_destinataire" name="localisation_destinataire" value="{{ old('localisation_destinataire') }}" maxlength="255" placeholder="Ex. Direction régionale de Kara"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                            </div>
                            <div>
                                <label for="date_effet" class="mb-1 block text-xs font-semibold text-slate-600">Date d’effet souhaitée</label>
                                <input type="date" id="date_effet" name="date_effet" value="{{ old('date_effet') }}"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="motif" class="mb-1 block text-xs font-semibold text-slate-600">Motif du mouvement</label>
                                <textarea id="motif" name="motif" rows="2" maxlength="2000" placeholder="Raison du transfert ou de l’affectation…"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">{{ old('motif') }}</textarea>
                            </div>
                            <div class="sm:col-span-2">
                                <button type="submit" class="rounded-lg bg-[#006a4f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#004d3a]">Demander l’affectation</button>
                                <p class="mt-2 text-xs text-slate-400">La demande devra être validée par un responsable habilité (ADMIN ou CHEF_SERVICE).</p>
                            </div>
                        </form>
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
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">État et maintenance</h2>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $bien->interventions->count() }}</span>
                </div>

                <div class="mt-4 space-y-4">
                    @forelse ($bien->interventions as $intervention)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-800">{{ $intervention->description }}</p>
                                @php
                                    $interventionStyles = [
                                        'EN_COURS' => 'bg-sky-50 text-sky-700',
                                        'TERMINEE' => 'bg-emerald-50 text-emerald-700',
                                        'ANNULEE' => 'bg-slate-200 text-slate-500',
                                    ];
                                    $interventionLabels = [
                                        'EN_COURS' => 'En cours',
                                        'TERMINEE' => 'Terminée',
                                        'ANNULEE' => 'Annulée',
                                    ];
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $interventionStyles[$intervention->statut] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $interventionLabels[$intervention->statut] ?? $intervention->statut }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $intervention->type }} · début le {{ $intervention->date_debut?->format('d/m/Y') }}
                                @if ($intervention->date_fin) · fin le {{ $intervention->date_fin->format('d/m/Y') }} @endif
                                @if ($intervention->prestataire) · {{ $intervention->prestataire }} @endif
                                @if ($intervention->cout !== null) · {{ number_format((float) $intervention->cout, 0, ',', ' ') }} FCFA @endif
                                · par {{ $intervention->auteur?->name ?? '—' }}
                            </p>
                            @if ($intervention->commentaire)
                                <p class="mt-1 text-xs text-slate-400">{{ $intervention->commentaire }}</p>
                            @endif

                            @can('manage-biens')
                                @if ($intervention->estEnCours() && $bien->is_active)
                                    <div class="mt-3 flex flex-col gap-2 border-t border-slate-200/70 pt-3 sm:flex-row sm:items-end">
                                        <form method="POST" action="{{ route('interventions.terminer', $intervention) }}" class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center">
                                            @csrf
                                            <select name="etat" class="flex-1 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs outline-none focus:border-[#006a4f]">
                                                <option value="">État après intervention…</option>
                                                @foreach (\App\Models\Bien::ETATS as $etat)
                                                    @if ($etat !== 'EN_REPARATION')
                                                        <option value="{{ $etat }}">{{ $etatLabels[$etat] ?? $etat }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <button type="submit" class="rounded-lg bg-[#006a4f] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#004d3a]">Terminer</button>
                                        </form>
                                        <form method="POST" action="{{ route('interventions.annuler', $intervention) }}" onsubmit="return confirm('Annuler cette intervention ?');">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:border-red-200 hover:text-red-600">Annuler</button>
                                        </form>
                                    </div>
                                @endif
                            @endcan
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-500">Aucune intervention enregistrée.</p>
                    @endforelse
                </div>

                @can('manage-biens')
                    @if ($bien->is_active && ! $bien->interventions->contains(fn ($i) => $i->estEnCours()))
                        <form method="POST" action="{{ route('interventions.store', $bien) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4">
                            @csrf
                            <p class="text-xs font-semibold text-slate-500">Nouvelle intervention</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Type</label>
                                    <select name="type" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                                        <option value="REPARATION">Réparation</option>
                                        <option value="MAINTENANCE">Maintenance</option>
                                        <option value="VISITE_TECHNIQUE">Visite technique</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Date de début</label>
                                    <input type="date" name="date_debut" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-600">Description</label>
                                <input type="text" name="description" required maxlength="2000" placeholder="Ex. Remplacement de la batterie" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Prestataire</label>
                                    <input type="text" name="prestataire" maxlength="255" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Coût estimé (FCFA)</label>
                                    <input type="number" name="cout" min="0" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                                </div>
                            </div>
                            <button type="submit" class="w-fit rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Ouvrir l’intervention</button>
                            <p class="text-xs text-slate-400">Le bien passera à l’état « En réparation » jusqu’à la clôture.</p>
                        </form>
                    @endif
                @endcan
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Historique des états</h2>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $bien->etatHistoriques->count() }}</span>
                </div>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($bien->etatHistoriques as $historique)
                        <div class="py-3">
                            <p class="text-sm text-slate-800">
                                <span class="font-medium">{{ $etatLabels[$historique->de_etat] ?? $historique->de_etat }}</span>
                                <span class="text-slate-400">→</span>
                                <span class="font-medium">{{ $etatLabels[$historique->vers_etat] ?? $historique->vers_etat }}</span>
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $historique->created_at?->format('d/m/Y à H:i') }}
                                · par {{ $historique->auteur?->name ?? '—' }}
                            </p>
                            @if ($historique->commentaire)
                                <p class="mt-1 text-xs text-slate-400">{{ $historique->commentaire }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500">Aucun changement d’état enregistré.</p>
                    @endforelse
                </div>

                @can('manage-biens')
                    @if ($bien->is_active)
                        <form method="POST" action="{{ route('biens.etat', $bien) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-2">
                            @csrf
                            <div>
                                <label for="etat" class="mb-1 block text-xs font-semibold text-slate-600">Nouvel état</label>
                                <select id="etat" name="etat" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                                    @foreach (\App\Models\Bien::ETATS as $etat)
                                        @if ($etat !== $bien->etat)
                                            <option value="{{ $etat }}">{{ $etatLabels[$etat] ?? $etat }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="commentaire-etat" class="mb-1 block text-xs font-semibold text-slate-600">Commentaire</label>
                                <input type="text" id="commentaire-etat" name="commentaire" maxlength="2000" placeholder="Motif du changement…"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                            </div>
                            <div class="sm:col-span-2">
                                <button type="submit" class="rounded-lg bg-[#006a4f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#004d3a]">Changer l’état</button>
                            </div>
                        </form>
                    @endif
                @endcan
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
            </section>
        </div>
    </div>
@endsection
