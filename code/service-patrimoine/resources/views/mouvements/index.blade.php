@extends('layouts.app')

@section('title', 'Affectations et transferts')
@section('header-label', 'Mouvements')

@php
    $statutStyles = [
        'EN_ATTENTE' => 'bg-amber-50 text-amber-700',
        'VALIDEE' => 'bg-emerald-50 text-emerald-700',
        'REJETEE' => 'bg-red-50 text-red-700',
        'ANNULEE' => 'bg-slate-100 text-slate-500',
    ];
    $statutLabels = [
        'EN_ATTENTE' => 'En attente',
        'VALIDEE' => 'Validée',
        'REJETEE' => 'Rejetée',
        'ANNULEE' => 'Annulée',
    ];
    $user = auth()->user();
    $canValidate = $user->can('validate-affectations');
@endphp

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @error('affectation')
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ $message }}</div>
    @enderror
    @error('motif_decision')
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ $message }}</div>
    @enderror

    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-[#006a4f]">Circuit de validation</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Affectations et transferts</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Demandes proposées par les gestionnaires, validées par les responsables habilités, avec historique complet.</p>
        </div>
        @if ($canValidate)
            <div class="self-start rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs font-semibold text-amber-800 sm:self-auto">
                Vous pouvez valider les demandes en attente
            </div>
        @endif
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('mouvements.index') }}" class="grid gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="q" class="mb-1 block text-xs font-semibold text-slate-500">Recherche bien</label>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="N° inventaire ou désignation…"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-[#006a4f] focus:ring-2 focus:ring-[#006a4f]/20">
            </div>
            <div>
                <label for="statut" class="mb-1 block text-xs font-semibold text-slate-500">Statut</label>
                <select id="statut" name="statut" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    <option value="">Tous</option>
                    @foreach ($statuts as $statut)
                        <option value="{{ $statut }}" @selected(($filters['statut'] ?? null) === $statut)>{{ $statutLabels[$statut] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="bien_id" class="mb-1 block text-xs font-semibold text-slate-500">Bien</label>
                <select id="bien_id" name="bien_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    <option value="">Tous</option>
                    @foreach ($biens as $bien)
                        <option value="{{ $bien->id }}" @selected(($filters['bien_id'] ?? null) == $bien->id)>{{ $bien->numero_inventaire }} — {{ $bien->designation }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-[#006a4f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#004d3a]">Filtrer</button>
            </div>
        </form>
    </section>

    <section class="mt-6 space-y-4">
        @forelse ($affectations as $affectation)
            <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('biens.show', $affectation->bien) }}" class="font-mono text-xs font-semibold text-[#006a4f] hover:underline">{{ $affectation->bien?->numero_inventaire }}</a>
                            <span class="text-xs text-slate-400">·</span>
                            <a href="{{ route('biens.show', $affectation->bien) }}" class="text-sm font-semibold text-slate-800 hover:text-[#006a4f]">{{ $affectation->bien?->designation }}</a>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statutStyles[$affectation->statut] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $statutLabels[$affectation->statut] ?? $affectation->statut }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600">
                            <span>
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">De</span>
                                <span class="font-medium">{{ $affectation->serviceOrigine?->nom_service ?? 'Non affecté' }}</span>
                                @if ($affectation->localisation_origine) <span class="text-xs text-slate-400">({{ $affectation->localisation_origine }})</span> @endif
                            </span>
                            <svg class="size-4 shrink-0 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                            <span>
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Vers</span>
                                <span class="font-medium">{{ $affectation->serviceDestinataire?->nom_service ?? '—' }}</span>
                                @if ($affectation->localisation_destinataire) <span class="text-xs text-slate-400">({{ $affectation->localisation_destinataire }})</span> @endif
                            </span>
                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            Demandée par <span class="font-medium">{{ $affectation->demandeur?->name ?? '—' }}</span>
                            le {{ $affectation->date_demande?->format('d/m/Y') }}
                            @if ($affectation->date_effet) · effet le {{ $affectation->date_effet->format('d/m/Y') }} @endif
                            @if ($affectation->date_fin) · clos le {{ $affectation->date_fin->format('d/m/Y') }} @endif
                            @if ($affectation->valideur) · par {{ $affectation->valideur->name }} le {{ $affectation->date_validation?->format('d/m/Y') }} @endif
                        </p>
                        @if ($affectation->motif)
                            <p class="mt-1 text-xs text-slate-400">Motif : {{ $affectation->motif }}</p>
                        @endif
                        @if ($affectation->motif_decision && $affectation->statut !== 'EN_ATTENTE')
                            <p class="mt-1 text-xs text-slate-400">Décision : {{ $affectation->motif_decision }}</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 flex-wrap items-start gap-2">
                        @if ($affectation->estEnAttente())
                            @can('validate-affectations')
                                @if ($affectation->demandeur_id !== $user->id)
                                    <form method="POST" action="{{ route('affectations.valider', $affectation) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-[#006a4f] px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-[#004d3a]">Valider</button>
                                    </form>
                                    <details class="relative">
                                        <summary class="cursor-pointer list-none rounded-lg border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100">Rejeter</summary>
                                        <form method="POST" action="{{ route('affectations.rejeter', $affectation) }}" class="absolute right-0 z-10 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-3 shadow-lg">
                                            @csrf
                                            <label class="mb-1 block text-xs font-semibold text-slate-600">Motif du rejet *</label>
                                            <input type="text" name="motif_decision" required maxlength="255" placeholder="Raison du rejet…"
                                                class="mb-2 w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs outline-none focus:border-red-300">
                                            <button type="submit" class="w-full rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white">Confirmer le rejet</button>
                                        </form>
                                    </details>
                                @else
                                    <span class="rounded-lg bg-slate-50 px-3 py-2 text-xs font-medium text-slate-400">À valider par un autre responsable</span>
                                @endif
                            @endcan
                            @if ($affectation->demandeur_id === $user->id || $user->role === 'ADMIN')
                                <form method="POST" action="{{ route('affectations.annuler', $affectation) }}" onsubmit="return confirm('Annuler cette demande ?');">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Annuler</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200/80 bg-white px-5 py-12 text-center shadow-sm">
                <p class="text-sm text-slate-500">Aucune affectation ne correspond à ces critères.</p>
                <p class="mt-1 text-xs text-slate-400">Les demandes se font depuis la fiche d’un bien, dans la section « Affectations et transferts ».</p>
            </div>
        @endforelse

        @if ($affectations->hasPages())
            <div class="pt-2">{{ $affectations->links() }}</div>
        @endif
    </section>
@endsection
