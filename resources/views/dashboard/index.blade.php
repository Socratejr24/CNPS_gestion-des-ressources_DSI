@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')

    <div class="p-6 bg-white rounded-xl border border-cnps-border mb-6">
        <h2 class="text-xl font-heading font-semibold text-cnps-blue mb-2">
            Bienvenue dans votre espace
        </h2>
        <p class="text-cnps-muted">
            Maquette de validation — le contenu réel sera implémenté après validation.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 bg-white rounded-xl border border-cnps-border">
            <div class="text-sm text-cnps-muted mb-1">Demandes en cours</div>
            <div class="text-2xl font-heading font-bold text-cnps-blue">0</div>
        </div>
        <div class="p-5 bg-white rounded-xl border border-cnps-border">
            <div class="text-sm text-cnps-muted mb-1">En attente</div>
            <div class="text-2xl font-heading font-bold text-cnps-orange">0</div>
        </div>
        <div class="p-5 bg-white rounded-xl border border-cnps-border">
            <div class="text-sm text-cnps-muted mb-1">Validées</div>
            <div class="text-2xl font-heading font-bold text-cnps-green">0</div>
        </div>
        <div class="p-5 bg-white rounded-xl border border-cnps-border">
            <div class="text-sm text-cnps-muted mb-1">Clôturées</div>
            <div class="text-2xl font-heading font-bold text-cnps-text">0</div>
        </div>
    </div>

@endsection