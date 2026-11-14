@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('header-label', 'Tableau de bord')

@section('content')
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-[#497e62]">Espace de gestion</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Bonjour, {{ $user->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Voici l’état des référentiels administratifs enregistrés dans le système.</p>
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

    <section id="referentiels" class="mt-10">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#497e62]">Données de référence</p>
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
                            <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg bg-[#edf5ee] text-xs font-bold text-[#285c48]">
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

    <section class="mt-8 flex flex-col gap-3 rounded-2xl border border-[#d7e8d9] bg-[#edf5ee] p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-white text-[#285c48]">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 3v18M3 12h18"/>
                </svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-[#214d3d]">La suite arrive progressivement</p>
                <p class="mt-1 text-xs leading-5 text-[#497e62]">L’inventaire des biens, les affectations et les transferts seront ajoutés dans les prochaines étapes du MVP.</p>
            </div>
        </div>
        <span class="shrink-0 rounded-lg bg-white/80 px-3 py-2 text-xs font-semibold text-[#497e62]">Prochaine étape · Inventaire</span>
    </section>
@endsection
