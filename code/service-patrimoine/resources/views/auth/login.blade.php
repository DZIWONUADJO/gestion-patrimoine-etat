<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion · SGPE</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/armoiries-togo.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#eef2ee] font-sans text-slate-900 antialiased">
    <div class="h-1.5 w-full bg-gradient-to-r from-gouv-vert via-gouv-or to-gouv-rouge"></div>

    <main class="grid min-h-[calc(100vh-6px)] place-items-center px-5 py-10">
        <div class="w-full max-w-md">
            <div class="rounded-2xl border border-slate-200 bg-white px-8 py-10 shadow-sm sm:px-10">
                <div class="text-center">
                    <img src="{{ asset('images/armoiries-togo.svg') }}" alt="Armoiries de la République Togolaise" class="mx-auto h-20 w-auto">
                    <p class="mt-5 font-serif text-sm font-semibold uppercase tracking-[0.28em] text-gouv-vert">République Togolaise</p>
                    <div class="mx-auto mt-3 h-px w-16 bg-gouv-or"></div>
                    <h1 class="mt-4 font-serif text-3xl font-bold tracking-tight text-slate-900">SGPE</h1>
                    <p class="mt-1 text-sm text-slate-500">Système de Gestion du Patrimoine de l’État</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Adresse e-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-gouv-vert focus:ring-2 focus:ring-gouv-vert/20"
                            placeholder="nom@administration.tg">
                        @error('email')
                            <p class="mt-2 text-sm text-gouv-rouge">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Mot de passe</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-gouv-vert focus:ring-2 focus:ring-gouv-vert/20"
                            placeholder="Votre mot de passe">
                        @error('password')
                            <p class="mt-2 text-sm text-gouv-rouge">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($errors->has('email') && ! old('email'))
                        <div class="rounded-lg border border-gouv-rouge/30 bg-gouv-rouge/5 px-4 py-3 text-sm text-gouv-rouge">{{ $errors->first('email') }}</div>
                    @endif

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-gouv-vert focus:ring-gouv-vert">
                        Garder ma session ouverte
                    </label>

                    <button type="submit" class="w-full rounded-lg bg-gouv-vert px-4 py-3 text-sm font-semibold text-white transition hover:bg-gouv-vert-fonce focus:outline-none focus:ring-2 focus:ring-gouv-vert/30">
                        Ouvrir ma session
                    </button>
                </form>
            </div>

            <div class="mt-6 text-center">
                <p class="font-serif text-xs italic tracking-wide text-slate-500">« Travail — Liberté — Patrie »</p>
                <p class="mt-2 text-xs text-slate-400">Accès réservé aux utilisateurs habilités · Version pilote</p>
            </div>
        </div>
    </main>
</body>
</html>
