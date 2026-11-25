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

    {{-- À faire selon les rôles et responsabilités --}}
    @include('dashboard.partials.travail', ['travail' => $travail])

    {{-- Indicateurs --}}
    @if($global)
        <p class="dash-perimetre"><x-icon name="info" size="xs" /> Vue administrateur : indicateurs calculés sur l'ensemble des demandes soumises.</p>
    @endif
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
