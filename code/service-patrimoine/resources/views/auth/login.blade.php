<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion · SGPE</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/armoiries-togo.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f7f5] font-sans text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="relative hidden overflow-hidden bg-[#103d32] px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-20">
            <div class="absolute -right-32 -top-32 size-[520px] rounded-full border border-white/10"></div>
            <div class="absolute -right-12 -top-12 size-[360px] rounded-full border border-white/10"></div>
            <div class="absolute -bottom-48 -left-20 size-[460px] rounded-full bg-[#c4e7a5]/10 blur-2xl"></div>

            <a href="{{ route('login') }}" class="relative flex items-center gap-3">
                <img src="{{ asset('images/armoiries-togo.svg') }}" alt="Emblème SGPE" class="h-14 w-auto drop-shadow">
                <span>
                    <span class="block text-lg font-bold tracking-wide">SGPE</span>
                    <span class="block text-xs text-emerald-100/70">Gestion du patrimoine de l’État</span>
                </span>
            </a>

            <div class="relative max-w-xl py-16">
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-semibold text-emerald-100">
                    <span class="size-1.5 rounded-full bg-[#c4e7a5]"></span>
                    Service public · République Togolaise
                </p>
                <h1 class="text-4xl font-semibold leading-tight tracking-tight xl:text-5xl">
                    Un patrimoine public mieux suivi, pour une action publique plus efficace.
                </h1>
                <p class="mt-6 max-w-lg text-base leading-7 text-emerald-50/70">
                    Retrouvez dans un espace sécurisé les référentiels administratifs et, progressivement, l’inventaire et le suivi des biens de l’État.
                </p>
                <div class="mt-10 flex items-center gap-3 text-sm text-emerald-50/70">
                    <span class="grid size-9 place-items-center rounded-xl bg-white/10">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M12 3 4.5 6v5.5c0 4.3 3.2 7.8 7.5 9.5 4.3-1.7 7.5-5.2 7.5-9.5V6L12 3Z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </span>
                    Accès réservé aux utilisateurs habilités
                </div>
            </div>

            <p class="relative text-xs text-emerald-50/45">SGPE · Version pilote en développement</p>
        </section>

        <section class="flex items-center justify-center px-5 py-12 sm:px-10">
            <div class="w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-12 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('images/armoiries-togo.svg') }}" alt="Emblème SGPE" class="h-12 w-auto drop-shadow-sm">
                    <span>
                        <span class="block text-lg font-bold text-[#103d32]">SGPE</span>
                        <span class="block text-xs text-slate-500">Gestion du patrimoine de l’État</span>
                    </span>
                </a>

                <div class="mb-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-[#497e62]">Bienvenue</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Connectez-vous</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Utilisez le compte qui vous a été attribué par l’administrateur.</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Adresse e-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#4a8067] focus:ring-4 focus:ring-[#4a8067]/10"
                            placeholder="nom@administration.tg">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Mot de passe</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#4a8067] focus:ring-4 focus:ring-[#4a8067]/10"
                            placeholder="Votre mot de passe">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($errors->has('email') && ! old('email'))
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('email') }}</div>
                    @endif

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-[#285c48] focus:ring-[#285c48]">
                        Garder ma session ouverte
                    </label>

                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#174b3d] px-4 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#103d32] focus:outline-none focus:ring-4 focus:ring-[#174b3d]/20">
                        Ouvrir ma session
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-8 border-t border-slate-200 pt-5 text-xs leading-5 text-slate-400">
                    La création de compte est réservée à l’administrateur. Si vous avez besoin d’un accès, contactez le responsable de votre administration.
                </div>
            </div>
        </section>
    </main>
</body>
</html>
