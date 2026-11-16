@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('header-label', 'Tableau de bord')

@section('content')
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-[#006a4f]">Espace de gestion</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Bonjour, {{ $user->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Vue d’ensemble du patrimoine : inventaire, mouvements, état des biens et contrôles.</p>
        </div>
        <div class="inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 sm:self-auto">
            <span class="size-1.5 rounded-full bg-emerald-500"></span>
            Environnement pilote
        </div>
    </section>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 {{ $assetCount !== null ? 'xl:grid-cols-3' : 'xl:grid-cols-2' }}" aria-label="Indicateurs">
        @if ($assetCount !== null)
            <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Biens enregistrés</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($assetCount, 0, ',', ' ') }}</p>
                    </div>
                    <span class="grid size-11 place-items-center rounded-xl bg-amber-50 text-amber-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/>
                            <path d="m4.5 7.8 7.5 4.3 7.5-4.3M12 12v8.5"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs text-slate-400">Total de la base patrimoine</p>
            </article>
        @endif

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Catégories actives</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($activeCategoryCount, 0, ',', ' ') }}</p>
                </div>
                <span class="grid size-11 place-items-center rounded-xl bg-sky-50 text-sky-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"/>
                        <path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20M8 7h8M8 10h6"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-slate-400">Référentiel des biens de l’État</p>
        </article>

        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:col-span-2 {{ $assetCount !== null ? 'xl:col-span-1' : 'xl:col-span-1' }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Services actifs</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($activeServiceCount, 0, ',', ' ') }}</p>
                </div>
                <span class="grid size-11 place-items-center rounded-xl bg-violet-50 text-violet-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-4h4v4"/>
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-slate-400">Arborescence administrative</p>
        </article>
    </section>

    <section class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Indicateurs d'activité">
        <a href="{{ route('mouvements.index') }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-amber-200 hover:shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Mouvements en attente</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight {{ $affectationsEnAttente > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ number_format($affectationsEnAttente, 0, ',', ' ') }}</p>
                </div>
                <span class="grid size-11 place-items-center rounded-xl bg-amber-50 text-amber-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 7h14M5 12h14M5 17h8"/><circle cx="18" cy="17" r="3"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-slate-400">Demandes d’affectation à valider</p>
        </a>

        <a href="{{ route('biens.index') }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-sky-200 hover:shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Interventions en cours</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight {{ $interventionsEnCours > 0 ? 'text-sky-600' : 'text-slate-900' }}">{{ number_format($interventionsEnCours, 0, ',', ' ') }}</p>
                </div>
                <span class="grid size-11 place-items-center rounded-xl bg-sky-50 text-sky-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14.7 6.3a4.5 4.5 0 0 0-6 5.6L3 17.6V21h3.4l5.7-5.7a4.5 4.5 0 0 0 5.6-6L14.5 13l-2.5-2.5 2.7-4.2Z"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-slate-400">Biens en maintenance ou réparation</p>
        </a>

        <a href="{{ route('inventaires.index') }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-emerald-200 hover:shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Inventaires en cours</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight {{ $inventairesEnCours > 0 ? 'text-emerald-600' : 'text-slate-900' }}">{{ number_format($inventairesEnCours, 0, ',', ' ') }}</p>
                </div>
                <span class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xs text-slate-400">Campagnes de contrôle actives</p>
        </a>
    </section>

    <section class="mt-10">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#006a4f]">État du parc</p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900">Répartition des biens actifs par état</h2>
            </div>
            <a href="{{ route('biens.index') }}" class="text-xs font-semibold text-[#006a4f] hover:underline">Voir l’inventaire →</a>
        </div>

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
                'BON_ETAT' => 'bg-emerald-500',
                'ETAT_MOYEN' => 'bg-amber-400',
                'A_REPARER' => 'bg-orange-500',
                'EN_REPARATION' => 'bg-sky-500',
                'HORS_SERVICE' => 'bg-slate-400',
                'INTROUVABLE' => 'bg-red-500',
            ];
            $totalActifs = $biensParEtat->sum();
        @endphp

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            @if ($totalActifs > 0)
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    @foreach ($etatLabels as $etat => $label)
                        @php $count = (int) ($biensParEtat[$etat] ?? 0); @endphp
                        @if ($count > 0)
                            <div class="{{ $etatStyles[$etat] }}" style="width: {{ round($count / $totalActifs * 100, 2) }}%" title="{{ $label }} : {{ $count }}"></div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($etatLabels as $etat => $label)
                        @php $count = (int) ($biensParEtat[$etat] ?? 0); @endphp
                        <a href="{{ route('biens.index', ['etat' => $etat]) }}" class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2.5 transition hover:border-slate-200 hover:bg-slate-50">
                            <span class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <span class="size-2.5 rounded-full {{ $etatStyles[$etat] }}"></span>
                                {{ $label }}
                            </span>
                            <span class="text-sm font-semibold text-slate-900">{{ number_format($count, 0, ',', ' ') }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="py-6 text-center text-sm text-slate-500">Aucun bien actif enregistré pour l’instant.</p>
            @endif
        </div>
    </section>

    <section class="mt-8 grid gap-5 xl:grid-cols-2">
        <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-800">Anomalies d’inventaire récentes</h3>
                    <p class="mt-1 text-xs text-slate-400">Biens déclarés en anomalie ou introuvables</p>
                </div>
                <a href="{{ route('inventaires.index') }}" class="text-xs font-semibold text-[#006a4f] hover:underline">Contrôles →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($anomaliesRecentes as $ligne)
                    <div class="px-5 py-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('biens.show', $ligne->bien) }}" class="truncate text-sm font-medium text-slate-800 hover:text-[#006a4f] hover:underline">
                                {{ $ligne->bien?->numero_inventaire }} — {{ $ligne->bien?->designation }}
                            </a>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $ligne->statut === 'INTROUVABLE' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $ligne->statut === 'INTROUVABLE' ? 'Introuvable' : 'Anomalie' }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $ligne->inventaire?->libelle }} · {{ $ligne->created_at?->format('d/m/Y') }}@if ($ligne->releveur) · par {{ $ligne->releveur->name }}@endif</p>
                        @if ($ligne->commentaire)
                            <p class="mt-1 line-clamp-2 text-xs text-slate-400">{{ $ligne->commentaire }}</p>
                        @endif
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Aucune anomalie enregistrée.</p>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-800">Derniers mouvements</h3>
                    <p class="mt-1 text-xs text-slate-400">Demandes d’affectation récentes</p>
                </div>
                <a href="{{ route('mouvements.index') }}" class="text-xs font-semibold text-[#006a4f] hover:underline">Tout voir →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($derniersMouvements as $mouvement)
                    <div class="px-5 py-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-sm font-medium text-slate-800">
                                {{ $mouvement->bien?->numero_inventaire }} — {{ $mouvement->bien?->designation }}
                            </p>
                            @php
                                $mvtStyles = [
                                    'EN_ATTENTE' => 'bg-amber-50 text-amber-700',
                                    'VALIDEE' => 'bg-emerald-50 text-emerald-700',
                                    'REJETEE' => 'bg-red-50 text-red-700',
                                    'ANNULEE' => 'bg-slate-100 text-slate-500',
                                ];
                                $mvtLabels = [
                                    'EN_ATTENTE' => 'En attente',
                                    'VALIDEE' => 'Validée',
                                    'REJETEE' => 'Rejetée',
                                    'ANNULEE' => 'Annulée',
                                ];
                            @endphp
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $mvtStyles[$mouvement->statut] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $mvtLabels[$mouvement->statut] ?? $mouvement->statut }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            → {{ $mouvement->serviceDestinataire?->nom_service ?? '—' }} · {{ $mouvement->date_demande?->format('d/m/Y') }}@if ($mouvement->demandeur) · par {{ $mouvement->demandeur->name }}@endif
                        </p>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Aucun mouvement enregistré.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section id="referentiels" class="mt-10">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#006a4f]">Données de référence</p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900">Référentiels administratifs</h2>
            </div>
            <span class="hidden text-xs text-slate-400 sm:inline">Données affichées depuis la base SGPE</span>
        </div>

        <div class="grid gap-5 xl:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="font-semibold text-slate-800">Catégories de biens</h3>
                        <p class="mt-1 text-xs text-slate-400">Les catégories actives du référentiel</p>
                    </div>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $activeCategoryCount }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        <div class="flex items-start gap-3 px-5 py-4">
                            <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg bg-[#eef4ef] text-xs font-bold text-[#006a4f]">
                                {{ mb_strtoupper(mb_substr($category->libelle ?: 'À', 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $category->libelle ?: 'Catégorie à compléter' }}</p>
                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $category->description ?: 'Aucune description renseignée.' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Aucune catégorie active n’est encore enregistrée.</p>
                    @endforelse
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="font-semibold text-slate-800">Services administratifs</h3>
                        <p class="mt-1 text-xs text-slate-400">Les premiers niveaux de l’arborescence</p>
                    </div>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $activeServiceCount }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($services as $service)
                        <div class="flex items-start justify-between gap-3 px-5 py-4">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg bg-[#f1effb] text-violet-700">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/>
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $service->nom_service ?: 'Service à compléter' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $service->type }}@if ($service->parent) · {{ $service->parent->nom_service }}@endif</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-md bg-slate-50 px-2 py-1 font-mono text-[10px] font-semibold text-slate-500">{{ $service->code_service ?: 'Code à compléter' }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Aucun service n’est encore enregistré.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>

    <section class="mt-8 flex flex-col gap-3 rounded-2xl border border-[#d7e8d9] bg-[#eef4ef] p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-white text-[#006a4f]">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 3v18M3 12h18"/>
                </svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-[#214d3d]">Le système est opérationnel</p>
                <p class="mt-1 text-xs leading-5 text-[#006a4f]">Inventaire des biens, affectations, maintenance, contrôles d’inventaire et rapports sont disponibles.</p>
            </div>
        </div>
        <a href="{{ route('rapports.index') }}" class="shrink-0 rounded-lg bg-white/80 px-3 py-2 text-xs font-semibold text-[#006a4f] transition hover:bg-white">Exporter un rapport →</a>
    </section>
@endsection
