@extends('layouts.app')

@section('title', 'Nouvel inventaire')
@section('header-label', 'Inventaires')

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <a href="{{ route('inventaires.index') }}" class="text-xs font-semibold text-[#497e62] hover:underline">← Inventaires</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Nouvel inventaire</h1>
    <p class="mt-1 text-sm text-slate-500">Vous serez enregistré comme agent responsable de la campagne.</p>

    <section class="mt-6 max-w-2xl rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('inventaires.store') }}" class="grid gap-4">
            @csrf
            <div>
                <label for="libelle" class="mb-1 block text-xs font-semibold text-slate-600">Libellé</label>
                <input type="text" id="libelle" name="libelle" value="{{ old('libelle') }}" required maxlength="255" placeholder="Ex. Inventaire annuel 2026 — Direction régionale de Lomé"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#497e62] @error('libelle') border-red-300 @enderror">
                @error('libelle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="lieu" class="mb-1 block text-xs font-semibold text-slate-600">Lieu</label>
                    <input type="text" id="lieu" name="lieu" value="{{ old('lieu') }}" maxlength="255"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#497e62]">
                </div>
                <div>
                    <label for="date" class="mb-1 block text-xs font-semibold text-slate-600">Date</label>
                    <input type="date" id="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#497e62]">
                </div>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-[#103d32] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0d3227]">Créer l’inventaire</button>
                <a href="{{ route('inventaires.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Annuler</a>
            </div>
        </form>
    </section>
@endsection
