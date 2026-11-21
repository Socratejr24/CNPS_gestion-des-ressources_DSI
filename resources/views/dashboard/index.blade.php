@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@push('styles')
    @vite('resources/css/dashboard/index.css')
@endpush

@section('content')

<div class="dashboard">

    {{-- Bienvenue + actions principales --}}
    @include('dashboard.partials.welcome', ['user' => $user])

    {{-- Indicateurs --}}
    @include('dashboard.partials.kpis', ['kpis' => $kpis])

    {{-- Graphiques --}}
    @include('dashboard.partials.charts')

    {{-- Activité récente --}}
    <div class="dash-lists-grid">
        @include('dashboard.partials.recent-demandes', ['recentesDemandes' => $recentesDemandes])

        <div class="dash-side">
            @include('dashboard.partials.recent-notifs', ['recentesNotifs' => $recentesNotifs])
            @include('dashboard.partials.circuit-info')
        </div>
    </div>

</div>

@endsection

@push('scripts')
    <script>
        window.dashboardData = {
            statuts: @json($chartStatuts),
            evolution: @json($chartEvolution),
        };
    </script>

    @vite('resources/js/dashboard/charts.js')
@endpush
