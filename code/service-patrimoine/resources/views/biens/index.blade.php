@extends('layouts.app')

@section('title', 'Inventaire des biens')
@section('header-label', 'Inventaire')

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
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-[#006a4f]">Inventaire du patrimoine</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Biens enregistrés</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Consultez, recherchez et suivez les biens de l’État rattachés aux services.</p>
        </div>
        @can('manage-biens')
            <a href="{{ route('biens.create') }}" class="inline-flex items-center gap-2 self-start rounded-xl bg-[#006a4f] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#004d3a] sm:self-auto">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nouveau bien
            </a>
        @endcan
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('biens.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label for="q" class="mb-1 block text-xs font-semibold text-slate-500">Recherche</label>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="N° inventaire, désignation, n° série…"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-[#006a4f] focus:ring-2 focus:ring-[#006a4f]/20">
            </div>
            <div>
                <label for="categorie_id" class="mb-1 block text-xs font-semibold text-slate-500">Catégorie</label>
                <select id="categorie_id" name="categorie_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    <option value="">Toutes</option>
                    @foreach ($categories as $categorie)
                        <option value="{{ $categorie->id }}" @selected(($filters['categorie_id'] ?? null) == $categorie->id)>{{ $categorie->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="service_id" class="mb-1 block text-xs font-semibold text-slate-500">Service</label>
                <select id="service_id" name="service_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    <option value="">Tous</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected(($filters['service_id'] ?? null) == $service->id)>{{ $service->nom_service }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="etat" class="mb-1 block text-xs font-semibold text-slate-500">État</label>
                <select id="etat" name="etat" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    <option value="">Tous</option>
                    @foreach ($etats as $etat)
                        <option value="{{ $etat }}" @selected(($filters['etat'] ?? null) === $etat)>{{ $etatLabels[$etat] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label for="is_active" class="mb-1 block text-xs font-semibold text-slate-500">Statut</label>
                    <select id="is_active" name="is_active" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                        <option value="">Tous</option>
                        <option value="1" @selected(($filters['is_active'] ?? null) === '1' || ($filters['is_active'] ?? null) === 1 || ($filters['is_active'] ?? null) === true)>Actifs</option>
                        <option value="0" @selected(($filters['is_active'] ?? null) === '0' || ($filters['is_active'] ?? null) === 0 || ($filters['is_active'] ?? null) === false)>Archivés</option>
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-[#006a4f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#004d3a]">Filtrer</button>
            </div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-semibold text-slate-800">Résultats</h2>
                <p class="mt-1 text-xs text-slate-400">{{ $biens->total() }} bien(s) correspondant aux critères</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-5 py-3 font-semibold">N° inventaire</th>
                        <th class="px-5 py-3 font-semibold">Désignation</th>
                        <th class="px-5 py-3 font-semibold">Catégorie</th>
                        <th class="px-5 py-3 font-semibold">Service</th>
                        <th class="px-5 py-3 font-semibold">État</th>
                        <th class="px-5 py-3 font-semibold">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($biens as $bien)
                        <tr class="transition hover:bg-slate-50/60">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('biens.show', $bien) }}" class="font-mono text-xs font-semibold text-[#006a4f] hover:underline">{{ $bien->numero_inventaire }}</a>
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('biens.show', $bien) }}" class="font-medium text-slate-800 hover:text-[#006a4f]">{{ $bien->designation }}</a>
                                @if ($bien->localisation)
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $bien->localisation }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $bien->categorie?->libelle ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $bien->service?->nom_service ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $etatStyles[$bien->etat] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $etatLabels[$bien->etat] ?? $bien->etat }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($bien->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Actif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Archivé</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                Aucun bien ne correspond à ces critères.
                                @can('manage-biens')
                                    <a href="{{ route('biens.create') }}" class="font-semibold text-[#006a4f] hover:underline">Enregistrer le premier bien</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($biens->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $biens->links() }}
            </div>
        @endif
    </section>
@endsection
