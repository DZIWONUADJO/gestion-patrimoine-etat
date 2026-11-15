@extends('layouts.app')

@section('title', $inventaire->libelle)
@section('header-label', 'Inventaire')

@php
    $ligneStyles = [
        'PRESENT' => 'bg-emerald-50 text-emerald-700',
        'INTROUVABLE' => 'bg-red-50 text-red-700',
        'ANOMALIE' => 'bg-amber-50 text-amber-700',
    ];
    $ligneLabels = [
        'PRESENT' => 'Présent',
        'INTROUVABLE' => 'Introuvable',
        'ANOMALIE' => 'Anomalie',
    ];
    $presentCount = $inventaire->lignes->where('statut', 'PRESENT')->count();
    $introuvables = $inventaire->lignes->where('statut', 'INTROUVABLE')->count();
    $anomalies = $inventaire->lignes->where('statut', 'ANOMALIE')->count();
@endphp

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
    @endif

    <a href="{{ route('inventaires.index') }}" class="text-xs font-semibold text-[#497e62] hover:underline">← Inventaires</a>

    <section class="mt-2 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $inventaire->libelle }}</h1>
                @if ($inventaire->estEnCours())
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">En cours</span>
                @else
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Clôturé</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-500">
                {{ $inventaire->lieu ?: 'Lieu non précisé' }} · {{ $inventaire->date?->format('d/m/Y') }}
                · agent : {{ $inventaire->agentResponsable?->name ?? '—' }}
                · créé par {{ $inventaire->createur?->name ?? '—' }}
            </p>
        </div>
        @if ($inventaire->estEnCours())
            @if ($inventaire->agent_responsable_id === auth()->id() || auth()->user()->role === 'ADMIN')
                <form method="POST" action="{{ route('inventaires.cloturer', $inventaire) }}" onsubmit="return confirm('Clôturer cet inventaire ? Les relevés ne pourront plus être modifiés.');">
                    @csrf
                    <button type="submit" class="w-fit rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">Clôturer l’inventaire</button>
                </form>
            @endif
        @endif
    </section>

    <div class="mt-6 grid gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Biens relevés</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $inventaire->lignes->count() }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Présents</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-800">{{ $presentCount }}</p>
        </div>
        <div class="rounded-2xl border border-red-200/80 bg-red-50/50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Introuvables</p>
            <p class="mt-2 text-2xl font-semibold text-red-800">{{ $introuvables }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200/80 bg-amber-50/50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Anomalies</p>
            <p class="mt-2 text-2xl font-semibold text-amber-800">{{ $anomalies }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm xl:col-span-2">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Biens relevés</h2>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($inventaire->lignes as $ligne)
                    <div class="flex flex-wrap items-center justify-between gap-2 py-3">
                        <div class="min-w-0">
                            <a href="{{ route('biens.show', $ligne->bien) }}" class="text-sm font-medium text-slate-800 hover:text-[#285c48] hover:underline">
                                {{ $ligne->bien?->numero_inventaire }} — {{ $ligne->bien?->designation }}
                            </a>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $ligne->bien?->service?->nom_service ?? '—' }}
                                · état : {{ $ligne->bien?->etat ? (['BON_ETAT' => 'Bon état', 'ETAT_MOYEN' => 'État moyen', 'A_REPARER' => 'À réparer', 'EN_REPARATION' => 'En réparation', 'HORS_SERVICE' => 'Hors service', 'INTROUVABLE' => 'Introuvable'][$ligne->bien->etat] ?? $ligne->bien->etat) : '—' }}
                                · relevé par {{ $ligne->releveur?->name ?? '—' }}
                            </p>
                            @if ($ligne->commentaire)
                                <p class="mt-1 text-xs text-slate-400">{{ $ligne->commentaire }}</p>
                            @endif
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $ligneStyles[$ligne->statut] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $ligneLabels[$ligne->statut] ?? $ligne->statut }}
                        </span>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">Aucun bien relevé pour l’instant.</p>
                @endforelse
            </div>
        </section>

        <section class="space-y-6">
            @if ($inventaire->estEnCours())
                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Relever un bien</h2>
                    <form method="POST" action="{{ route('inventaires.lignes.store', $inventaire) }}" class="mt-4 grid gap-3">
                        @csrf
                        <div>
                            <label for="bien_id" class="mb-1 block text-xs font-semibold text-slate-600">Bien</label>
                            <select id="bien_id" name="bien_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#497e62]">
                                <option value="">Choisir…</option>
                                @foreach ($biens as $bien)
                                    <option value="{{ $bien->id }}">{{ $bien->numero_inventaire }} — {{ $bien->designation }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="statut" class="mb-1 block text-xs font-semibold text-slate-600">Constat</label>
                            <select id="statut" name="statut" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#497e62]">
                                <option value="PRESENT">Présent</option>
                                <option value="ANOMALIE">Anomalie</option>
                                <option value="INTROUVABLE">Introuvable</option>
                            </select>
                        </div>
                        <div>
                            <label for="commentaire" class="mb-1 block text-xs font-semibold text-slate-600">Commentaire</label>
                            <textarea id="commentaire" name="commentaire" rows="2" maxlength="2000" placeholder="Détail du constat…"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#497e62]">{{ old('commentaire') }}</textarea>
                        </div>
                        @error('bien_id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('statut')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('inventaire')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        <button type="submit" class="rounded-lg bg-[#103d32] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0d3227]">Enregistrer le relevé</button>
                        <p class="text-xs text-slate-400">Un bien déclaré « Introuvable » passera automatiquement à l’état INTROUVABLE.</p>
                    </form>
                </section>
            @else
                <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Clôture</h2>
                    <p class="mt-3 text-sm text-slate-500">Cet inventaire est clôturé : les relevés sont figés.</p>
                </section>
            @endif
        </section>
    </div>
@endsection
