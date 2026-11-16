<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') · SGPE</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/armoiries-togo.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f6f4] font-sans text-slate-900 antialiased">
    <div class="h-1 w-full bg-gradient-to-r from-gouv-vert via-gouv-or to-gouv-rouge"></div>
    <div class="min-h-[calc(100vh-4px)] lg:grid lg:grid-cols-[260px_1fr]">
        <aside class="flex flex-col bg-gouv-vert text-white">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 border-b border-white/10 px-6 py-5">
                <img src="{{ asset('images/armoiries-togo.svg') }}" alt="Emblème SGPE" class="h-8 w-auto">
                <span>
                    <span class="block font-serif text-lg font-bold tracking-wide">SGPE</span>
                    <span class="block text-[11px] text-white/60">République Togolaise</span>
                </span>
            </a>

            <div class="px-6 pb-2 pt-5 text-[10px] font-bold uppercase tracking-[0.2em] text-gouv-or/80">Espace de travail</div>
            <nav class="space-y-1 px-3" aria-label="Navigation principale">
                <a href="{{ route('dashboard') }}" @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition',
                    'bg-white/12 text-white shadow-[inset_3px_0_0_0_#ffcf11]' => request()->routeIs('dashboard'),
                    'text-white/70 hover:bg-white/8 hover:text-white' => ! request()->routeIs('dashboard'),
                ])>
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M3 10.5 12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5v-9Z"/>
                        <path d="M9 21v-7h6v7"/>
                    </svg>
                    Vue d’ensemble
                </a>
                <a href="#referentiels" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-white/70 transition hover:bg-white/8 hover:text-white">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"/>
                        <path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20M8 7h8M8 10h6"/>
                    </svg>
                    Référentiels
                </a>
                <a href="{{ route('biens.index') }}" @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition',
                    'bg-white/12 text-white shadow-[inset_3px_0_0_0_#ffcf11]' => request()->routeIs('biens.*'),
                    'text-white/70 hover:bg-white/8 hover:text-white' => ! request()->routeIs('biens.*'),
                ])>
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/>
                        <path d="m4.5 7.8 7.5 4.3 7.5-4.3M12 12v8.5"/>
                    </svg>
                    Inventaire
                </a>
                <a href="{{ route('mouvements.index') }}" @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition',
                    'bg-white/12 text-white shadow-[inset_3px_0_0_0_#ffcf11]' => request()->routeIs('mouvements.*', 'affectations.*'),
                    'text-white/70 hover:bg-white/8 hover:text-white' => ! request()->routeIs('mouvements.*', 'affectations.*'),
                ])>
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M5 7h14M5 12h14M5 17h8"/>
                        <circle cx="18" cy="17" r="3"/>
                    </svg>
                    Mouvements
                </a>
                <a href="{{ route('inventaires.index') }}" @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition',
                    'bg-white/12 text-white shadow-[inset_3px_0_0_0_#ffcf11]' => request()->routeIs('inventaires.*'),
                    'text-white/70 hover:bg-white/8 hover:text-white' => ! request()->routeIs('inventaires.*'),
                ])>
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="m9 11 3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Contrôles
                </a>
                <a href="{{ route('rapports.index') }}" @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition',
                    'bg-white/12 text-white shadow-[inset_3px_0_0_0_#ffcf11]' => request()->routeIs('rapports.*'),
                    'text-white/70 hover:bg-white/8 hover:text-white' => ! request()->routeIs('rapports.*'),
                ])>
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M12 3v12m0 0 4-4m-4 4-4-4"/>
                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>
                    </svg>
                    Rapports
                </a>
            </nav>

            <div class="mt-auto p-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center gap-2 text-xs font-semibold text-gouv-or">
                        <span class="size-2 rounded-full bg-gouv-or"></span>
                        Version pilote
                    </div>
                    <p class="mt-2 text-xs leading-5 text-white/60">Les modules sont livrés progressivement et les données viennent de la base de l’application.</p>
                </div>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="flex items-center justify-between border-b border-slate-200/80 bg-white px-5 py-4 sm:px-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Togo <span class="mx-1 text-slate-300">/</span> Administration du patrimoine</p>
                    <p class="mt-1 text-sm font-semibold text-slate-700">@yield('header-label', 'Espace sécurisé')</p>
                </div>
                <div class="flex items-center gap-3">
                    @php $user = auth()->user(); @endphp
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ str_replace('_', ' ', $user->role) }}</p>
                    </div>
                    <div class="grid size-10 place-items-center rounded-full bg-gouv-vert/10 text-sm font-bold text-gouv-vert">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
