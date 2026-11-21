<!DOCTYPE html>
<html lang="fr">
<head>


<script>
        // Initialisation du thème AVANT le rendu (évite le flash)
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon CNPS --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-cnps.jpeg') }}?v=3">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-cnps.jpeg') }}?v=3">

    <title>@yield('title', 'Tableau de bord') — Gestion des ressources DSI · CNPS</title>

    @vite([
        'resources/css/app.css',
        'resources/css/layouts/app.css',
        'resources/css/layouts/sidebar.css',
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

    <div class="app-shell" x-data="appLayout()">

        {{-- Loader global --}}
        @include('layouts.partials.loader')

        {{-- Overlay mobile (sidebar) --}}
        <div class="app-overlay"
             :class="sidebarOpen && window.innerWidth < 1024 ? 'app-overlay--visible' : ''"
             @click="closeSidebarOnMobile()"
             x-show="sidebarOpen"
             x-cloak></div>

        {{-- Sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Zone principale --}}
        <div class="app-main">

            {{-- Topbar --}}
            @include('layouts.partials.topbar', ['pageTitle' => View::getSection('page-title') ?: 'Tableau de bord'])

            {{-- Contenu --}}
            <main class="app-content">
                @yield('content')
            </main>

            {{-- Footer --}}
            @include('layouts.partials.footer')

        </div>

        {{-- Drawers --}}
        @include('layouts.partials.notifications-drawer')
        @include('layouts.partials.profile-drawer')
        @include('layouts.partials.settings-drawer')

        {{-- Modal déconnexion --}}
        @include('layouts.partials.logout-modal')

        {{-- Messages flash Laravel → toasts (lus par toastManager au démarrage) --}}
        @if(session('success'))
            <div hidden data-flash-toast="{{ json_encode(['type' => 'success', 'title' => session('success')]) }}"></div>
        @endif
        @if(session('warning'))
            <div hidden data-flash-toast="{{ json_encode(['type' => 'warning', 'title' => session('warning')]) }}"></div>
        @endif

        {{-- Container des toasts (global) --}}
        <x-toast-container />
    </div>

    @stack('scripts')

</body>
</html>