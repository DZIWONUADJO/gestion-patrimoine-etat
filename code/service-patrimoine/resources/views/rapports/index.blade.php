@extends('layouts.app')

@section('title', 'Rapports')
@section('header-label', 'Rapports')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Rapports</h1>
    <p class="mt-1 text-sm text-slate-500">Exports de l’inventaire des biens et de l’historique des mouvements, au format PDF ou tableur (CSV).</p>

    <div class="mt-8 grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Inventaire des biens</h2>
            <p class="mt-2 text-sm text-slate-500">Liste complète des biens avec catégorie, service responsable, état et caractéristiques.</p>

            <form method="GET" action="{{ route('rapports.biens') }}" class="mt-4 grid gap-3">
                <div class="grid gap-3 sm:grid-cols-2">
                    <input type="text" name="q" placeholder="Recherche (n°, désignation, série)…" value="{{ request('q') }}"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    <select name="etat" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                        <option value="">Tous les états</option>
                        @foreach (['BON_ETAT' => 'Bon état', 'ETAT_MOYEN' => 'État moyen', 'A_REPARER' => 'À réparer', 'EN_REPARATION' => 'En réparation', 'HORS_SERVICE' => 'Hors service', 'INTROUVABLE' => 'Introuvable'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('etat') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="categorie_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                        <option value="">Toutes les catégories</option>
                        @foreach (\App\Models\Categorie::where('is_active', true)->orderBy('libelle')->get() as $categorie)
                            <option value="{{ $categorie->id }}" @selected((string) request('categorie_id') === (string) $categorie->id)>{{ $categorie->libelle }}</option>
                        @endforeach
                    </select>
                    <select name="service_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                        <option value="">Tous les services</option>
                        @foreach (\App\Models\Service::where('is_active', true)->orderBy('nom_service')->get() as $service)
                            <option value="{{ $service->id }}" @selected((string) request('service_id') === (string) $service->id)>{{ $service->nom_service }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                        <input type="checkbox" name="is_active" value="1" @checked(request('is_active') === '1') class="rounded border-slate-300 text-[#006a4f]"> Biens actifs uniquement
                    </label>
                    <button type="submit" class="rounded-lg bg-[#006a4f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#004d3a]">Télécharger en PDF</button>
                    <button type="submit" name="format" value="csv" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Exporter en CSV</button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Affectations et transferts</h2>
            <p class="mt-2 text-sm text-slate-500">Historique des demandes de mouvement : circuits de validation, rejets et annulations.</p>

            <form method="GET" action="{{ route('rapports.mouvements') }}" class="mt-4 grid gap-3">
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="statut" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                        <option value="">Tous les statuts</option>
                        @foreach (['EN_ATTENTE' => 'En attente', 'VALIDEE' => 'Validée', 'REJETEE' => 'Rejetée', 'ANNULEE' => 'Annulée'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('statut') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="bien_id" placeholder="ID du bien (optionnel)" value="{{ request('bien_id') }}"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-lg bg-[#006a4f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#004d3a]">Télécharger en PDF</button>
                    <button type="submit" name="format" value="csv" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Exporter en CSV</button>
                </div>
            </form>
        </section>
    </div>
@endsection
