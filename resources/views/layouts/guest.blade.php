<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon CNPS --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-cnps.jpeg') }}?v=3">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-cnps.jpeg') }}?v=3">

    <title>@yield('title', 'Connexion') — Gestion des ressources DSI · CNPS</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('styles')
</head>
<body class="min-h-screen">

    {{-- Bandeau quadricolore --}}
    <div class="h-1.5 flex">
        <span class="flex-1 bg-cnps-blue"></span>
        <span class="flex-1 bg-cnps-orange"></span>
        <span class="flex-1 bg-cnps-green"></span>
        <span class="flex-1 bg-cnps-yellow"></span>
    </div>

    @yield('content')

    @stack('scripts')

    {{-- Détection hors-connexion --}}
    <script>
        window.addEventListener('offline', () => {
            window.location.href = '/offline.html';
        });

        if (!navigator.onLine) {
            window.location.href = '/offline.html';
        }
    </script>

</body>
</html>