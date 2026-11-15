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
<body class="min-h-screen bg-[#f5f7f5] font-sans text-slate-900 antialiased">
    <main class="grid min-h-screen place-items-center px-5 py-12">
        <div class="relative w-full max-w-lg text-center">
            <img src="{{ asset('images/armoiries-togo.svg') }}" alt="" aria-hidden="true"
                class="pointer-events-none absolute left-1/2 top-1/2 h-80 w-auto -translate-x-1/2 -translate-y-1/2 opacity-[0.08]">

            <div class="relative">
                <img src="{{ asset('images/armoiries-togo.svg') }}" alt="Armoiries de la République Togolaise" class="mx-auto h-24 w-auto drop-shadow">

                <p class="mt-6 text-sm font-semibold uppercase tracking-[0.2em] text-[#497e62]">Erreur {{ $code }}</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-tight text-[#103d32] sm:text-5xl">{{ $title }}</h1>
                <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#174b3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#103d32] focus:outline-none focus:ring-4 focus:ring-[#174b3d]/20">
                        Retour à l’accueil
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M19 12H5M11 6l-6 6 6 6"/>
                        </svg>
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#4a8067] hover:text-[#174b3d]">
                        Page de connexion
                    </a>
                </div>

                <p class="mt-8 text-xs text-slate-400">SGPE · Système de Gestion du Patrimoine de l’État</p>
            </div>
        </div>
    </main>
</body>
</html>
