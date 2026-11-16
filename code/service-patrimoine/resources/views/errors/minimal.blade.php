@php
    $code = $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
        ? $exception->getStatusCode()
        : 500;

    $texts = [
        403 => ['Accès refusé', 'Votre compte ne dispose pas des droits nécessaires pour consulter cette page. Si vous pensez qu’il s’agit d’une erreur, contactez l’administrateur.'],
        404 => ['Page introuvable', 'La page que vous recherchez n’existe pas ou a été déplacée. Vérifiez l’adresse ou revenez à l’accueil.'],
        419 => ['Session expirée', 'Votre session a expiré par mesure de sécurité. Reconnectez-vous pour continuer.'],
        429 => ['Trop de requêtes', 'Vous avez effectué trop d’actions en peu de temps. Patientez quelques instants puis réessayez.'],
        503 => ['Service indisponible', 'Le service est momentanément indisponible, probablement pour maintenance. Réessayez dans quelques minutes.'],
    ];

    [$title, $description] = $texts[$code] ?? ['Une erreur est survenue', 'Une erreur inattendue s’est produite. Si le problème persiste, contactez le support technique et indiquez le code ci-dessus.'];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Erreur {{ $code }} · SGPE</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/armoiries-togo.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#eef2ee] font-sans text-slate-900 antialiased">
    <div class="h-1.5 w-full bg-gradient-to-r from-gouv-vert via-gouv-or to-gouv-rouge"></div>

    <main class="grid min-h-[calc(100vh-6px)] place-items-center px-5 py-10">
        <div class="relative w-full max-w-lg text-center">
            <img src="{{ asset('images/armoiries-togo.svg') }}" alt="" aria-hidden="true"
                class="pointer-events-none absolute left-1/2 top-1/2 h-80 w-auto -translate-x-1/2 -translate-y-1/2 opacity-[0.05]">

            <div class="relative rounded-2xl border border-slate-200 bg-white px-8 py-10 shadow-sm">
                <img src="{{ asset('images/armoiries-togo.svg') }}" alt="Armoiries de la République Togolaise" class="mx-auto h-16 w-auto">

                <p class="mt-5 text-xs font-bold uppercase tracking-[0.24em] text-gouv-vert">Erreur {{ $code }}</p>
                <div class="mx-auto mt-3 h-px w-12 bg-gouv-or"></div>
                <h1 class="mt-4 font-serif text-3xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
                <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gouv-vert px-5 py-3 text-sm font-semibold text-white transition hover:bg-gouv-vert-fonce focus:outline-none focus:ring-2 focus:ring-gouv-vert/30">
                        Retour à l’accueil
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M19 12H5M11 6l-6 6 6 6"/>
                        </svg>
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-gouv-vert hover:text-gouv-vert">
                        Page de connexion
                    </a>
                </div>
            </div>

            <p class="relative mt-6 font-serif text-xs italic tracking-wide text-slate-500">« Travail — Liberté — Patrie »</p>
            <p class="relative mt-1 text-xs text-slate-400">SGPE · Système de Gestion du Patrimoine de l’État</p>
        </div>
    </main>
</body>
</html>
