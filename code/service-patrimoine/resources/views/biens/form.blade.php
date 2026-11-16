@extends('layouts.app')

@section('title', $bien->exists ? 'Modifier le bien' : 'Nouveau bien')
@section('header-label', 'Inventaire')

@section('content')
    <section class="mb-6">
        <a href="{{ $bien->exists ? route('biens.show', $bien) : route('biens.index') }}" class="text-xs font-semibold text-[#006a4f] hover:underline">← Retour</a>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $bien->exists ? 'Modifier le bien' : 'Enregistrer un bien' }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Renseignez les caractéristiques du bien et son rattachement administratif.</p>
    </section>

    <form method="POST" action="{{ $bien->exists ? route('biens.update', $bien) : route('biens.store') }}" class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($bien->exists)
            @method('PATCH')
        @endif

        <div class="space-y-6 xl:col-span-2">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Identification</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="numero_inventaire" class="mb-1 block text-xs font-semibold text-slate-600">N° d’inventaire *</label>
                        <input type="text" id="numero_inventaire" name="numero_inventaire" value="{{ old('numero_inventaire', $bien->numero_inventaire) }}" required maxlength="100"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 font-mono text-sm outline-none transition focus:border-[#006a4f] focus:ring-2 focus:ring-[#006a4f]/20 @error('numero_inventaire') border-red-300 @enderror">
                        @error('numero_inventaire')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="designation" class="mb-1 block text-xs font-semibold text-slate-600">Désignation *</label>
                        <input type="text" id="designation" name="designation" value="{{ old('designation', $bien->designation) }}" required maxlength="255"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-[#006a4f] focus:ring-2 focus:ring-[#006a4f]/20 @error('designation') border-red-300 @enderror">
                        @error('designation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="mb-1 block text-xs font-semibold text-slate-600">Description</label>
                        <textarea id="description" name="description" rows="3" maxlength="2000"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-[#006a4f] focus:ring-2 focus:ring-[#006a4f]/20">{{ old('description', $bien->description) }}</textarea>
                    </div>
                    <div>
                        <label for="categorie_id" class="mb-1 block text-xs font-semibold text-slate-600">Catégorie *</label>
                        <select id="categorie_id" name="categorie_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f] @error('categorie_id') border-red-300 @enderror">
                            <option value="">Choisir…</option>
                            @foreach ($categories as $categorie)
                                <option value="{{ $categorie->id }}" @selected((string) old('categorie_id', $bien->categorie_id) === (string) $categorie->id)>{{ $categorie->libelle }}</option>
                            @endforeach
                        </select>
                        @error('categorie_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="service_id" class="mb-1 block text-xs font-semibold text-slate-600">Service responsable *</label>
                        <select id="service_id" name="service_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f] @error('service_id') border-red-300 @enderror">
                            <option value="">Choisir…</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((string) old('service_id', $bien->service_id) === (string) $service->id)>{{ $service->nom_service }}</option>
                            @endforeach
                        </select>
                        @error('service_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Caractéristiques</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="marque" class="mb-1 block text-xs font-semibold text-slate-600">Marque</label>
                        <input type="text" id="marque" name="marque" value="{{ old('marque', $bien->marque) }}" maxlength="100"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    </div>
                    <div>
                        <label for="modele" class="mb-1 block text-xs font-semibold text-slate-600">Modèle</label>
                        <input type="text" id="modele" name="modele" value="{{ old('modele', $bien->modele) }}" maxlength="100"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    </div>
                    <div>
                        <label for="numero_serie" class="mb-1 block text-xs font-semibold text-slate-600">N° de série</label>
                        <input type="text" id="numero_serie" name="numero_serie" value="{{ old('numero_serie', $bien->numero_serie) }}" maxlength="100"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 font-mono text-sm outline-none focus:border-[#006a4f]">
                    </div>
                    <div>
                        <label for="localisation" class="mb-1 block text-xs font-semibold text-slate-600">Localisation</label>
                        <input type="text" id="localisation" name="localisation" value="{{ old('localisation', $bien->localisation) }}" maxlength="255"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Acquisition</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="date_acquisition" class="mb-1 block text-xs font-semibold text-slate-600">Date d’acquisition</label>
                        <input type="date" id="date_acquisition" name="date_acquisition" value="{{ old('date_acquisition', $bien->date_acquisition?->toDateString()) }}"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    </div>
                    <div>
                        <label for="valeur_acquisition" class="mb-1 block text-xs font-semibold text-slate-600">Valeur (FCFA)</label>
                        <input type="number" id="valeur_acquisition" name="valeur_acquisition" value="{{ old('valeur_acquisition', $bien->valeur_acquisition) }}" min="0" step="1"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    </div>
                    <div>
                        <label for="fournisseur" class="mb-1 block text-xs font-semibold text-slate-600">Fournisseur / source</label>
                        <input type="text" id="fournisseur" name="fournisseur" value="{{ old('fournisseur', $bien->fournisseur) }}" maxlength="255"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">État</h2>
                <div class="mt-4">
                    <label for="etat" class="mb-1 block text-xs font-semibold text-slate-600">État du bien</label>
                    <select id="etat" name="etat" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#006a4f]">
                        @foreach ($etats as $etat)
                            <option value="{{ $etat }}" @selected(old('etat', $bien->etat ?? 'BON_ETAT') === $etat)>{{ $etat }}</option>
                        @endforeach
                    </select>
                    @error('etat')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-400">L’historique détaillé des changements d’état sera disponible avec le module de maintenance.</p>
            </section>

            <section class="rounded-2xl border border-[#d7e8d9] bg-[#eef4ef] p-6">
                <h2 class="text-sm font-semibold text-[#214d3d]">Avant d’enregistrer</h2>
                <ul class="mt-3 space-y-2 text-xs leading-5 text-[#006a4f]">
                    <li>· Le numéro d’inventaire doit être unique.</li>
                    <li>· Le bien doit être rattaché à un service responsable.</li>
                    <li>· Les justificatifs (factures, PV) s’ajoutent depuis la fiche du bien.</li>
                </ul>
            </section>

            <button type="submit" class="w-full rounded-xl bg-[#006a4f] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#004d3a]">
                {{ $bien->exists ? 'Enregistrer les modifications' : 'Enregistrer le bien' }}
            </button>
            <a href="{{ $bien->exists ? route('biens.show', $bien) : route('biens.index') }}" class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Annuler</a>
        </div>
    </form>
@endsection
