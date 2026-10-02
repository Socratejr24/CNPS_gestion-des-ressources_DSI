@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@push('styles')
    @vite('resources/css/dashboard/index.css')
@endpush

@section('content')

<div class="dashboard" x-data="dashboardPage()">

    {{-- Bandeau de bienvenue --}}
    @include('dashboard.partials.welcome', ['user' => $user])

    {{-- KPI --}}
    @include('dashboard.partials.kpis', ['kpis' => $kpis])

    {{-- Graphiques --}}
    @include('dashboard.partials.charts', [
        'chartStatuts' => $chartStatuts,
        'chartEvolution' => $chartEvolution,
        'chartTopRessources' => $chartTopRessources,
    ])

    {{-- Actions rapides --}}
    @include('dashboard.partials.quick-actions', ['quickActions' => $quickActions])

    {{-- Listes --}}
    <div class="dash-lists-grid">
        @include('dashboard.partials.recent-demandes', ['recentesDemandes' => $recentesDemandes])
        @include('dashboard.partials.recent-notifs', ['recentesNotifs' => $recentesNotifs])
    </div>

    {{-- Bandeau info circuit --}}
    @include('dashboard.partials.circuit-info')

</div>

@endsection

@push('scripts')
    {{-- Données pour ApexCharts --}}
    <script>
        window.dashboardData = {
            statuts: @json($chartStatuts),
            evolution: @json($chartEvolution),
            topRessources: @json($chartTopRessources),
        };
    </script>

    @vite('resources/js/dashboard/charts.js')
@endpush