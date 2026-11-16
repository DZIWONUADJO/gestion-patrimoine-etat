@extends('layouts.app')

@section('title', 'Contrôles et inventaires')
@section('header-label', 'Inventaires')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
    @endif

    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Inventaires</h1>
            <p class="mt-1 text-sm text-slate-500">Campagnes de contrôle : confirmation de présence et signalements d’anomalies.</p>
        </div>
        <a href="{{ route('inventaires.create') }}" class="w-fit rounded-xl bg-[#006a4f] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#004d3a]">Nouvel inventaire</a>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/60 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Libellé</th>
                        <th class="px-5 py-3">Lieu</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Agent responsable</th>
                        <th class="px-5 py-3">Biens relevés</th>
                        <th class="px-5 py-3">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inventaires as $inventaire)
                        <tr class="transition hover:bg-slate-50/60">
                            <td class="px-5 py-3.5 font-medium text-slate-800">
                                <a href="{{ route('inventaires.show', $inventaire) }}" class="hover:text-[#006a4f] hover:underline">{{ $inventaire->libelle }}</a>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $inventaire->lieu ?: '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $inventaire->date?->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $inventaire->agentResponsable?->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $inventaire->lignes_count }}</td>
                            <td class="px-5 py-3.5">
                                @if ($inventaire->estEnCours())
                                    <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">En cours</span>
                                @else
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Clôturé</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('inventaires.show', $inventaire) }}" class="text-xs font-semibold text-[#006a4f] hover:underline">Ouvrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">Aucun inventaire pour l’instant.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4">{{ $inventaires->links() }}</div>
@endsection
