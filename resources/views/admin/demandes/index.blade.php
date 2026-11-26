@extends('layouts.app')

@section('title', 'Toutes les demandes')
@section('page-title', 'Toutes les demandes')

@push('styles')
    @vite(['resources/css/admin/index.css', 'resources/css/demandes/index.css'])
@endpush

@php($filtresActifs = collect($filtres)->filter(fn ($v) => filled($v))->isNotEmpty())

@section('content')
<div class="adm-page">

    <div class="adm-header">
        <div>
            <h1 class="adm-title">Toutes les demandes</h1>
            <p class="adm-subtitle">
                Consultation de l'ensemble des demandes soumises (les brouillons restent privés à leur auteur).
                Vue en lecture seule : les décisions restent réservées aux acteurs du circuit.
            </p>
        </div>
        <x-button :href="route('admin.demandes.export', request()->query())" variant="outline" icon="download">Exporter (CSV)</x-button>
    </div>

    <div class="adm-kpis">
        <a href="{{ route('admin.demandes.index') }}" class="adm-kpi">
            <span class="adm-kpi__value">{{ $compteurs['total'] }}</span>
            <span class="adm-kpi__label">Demandes soumises</span>
        </a>
        <a href="{{ route('admin.demandes.index', ['etat' => 'EN_COURS']) }}" class="adm-kpi">
            <span class="adm-kpi__value">{{ $compteurs['en_cours'] }}</span>
            <span class="adm-kpi__label">En cours</span>
        </a>
        <a href="{{ route('admin.demandes.index', ['etat' => 'CLOTUREE']) }}" class="adm-kpi">
            <span class="adm-kpi__value">{{ $compteurs['cloturees'] }}</span>
            <span class="adm-kpi__label">Clôturées</span>
        </a>
        <a href="{{ route('admin.demandes.index', ['etat' => 'TERMINEES']) }}" class="adm-kpi">
            <span class="adm-kpi__value">{{ $compteurs['echecs'] }}</span>
            <span class="adm-kpi__label">Rejetées ou impossibles</span>
        </a>
    </div>

    <form method="GET" action="{{ route('admin.demandes.index') }}" class="adm-filters">
        <input type="search" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Référence, objet, demandeur ou matricule…"
               class="adm-filters__search" aria-label="Rechercher">

        <select name="etat" class="adm-filters__select" aria-label="État">
            <option value="">Tous les états</option>
            <option value="EN_COURS" @selected(($filtres['etat'] ?? '') === 'EN_COURS')>En cours (non terminées)</option>
            <option value="TERMINEES" @selected(($filtres['etat'] ?? '') === 'TERMINEES')>Terminées (états finaux)</option>
            <optgroup label="Statut précis">
                @foreach($statuts as $statut)
                    <option value="{{ $statut->value }}" @selected(($filtres['etat'] ?? '') === $statut->value)>{{ $statut->label() }}</option>
                @endforeach
            </optgroup>
        </select>

        <select name="departement_id" class="adm-filters__select" aria-label="Département du demandeur">
            <option value="">Tous les départements</option>
            @foreach($departements as $departement)
                <option value="{{ $departement->id }}" @selected((string) ($filtres['departement_id'] ?? '') === (string) $departement->id)>{{ $departement->libelle }}</option>
            @endforeach
        </select>

        <select name="destination_type" class="adm-filters__select" aria-label="Type de destination">
            <option value="">Toutes destinations</option>
            @foreach($types as $type)
                <option value="{{ $type->value }}" @selected(($filtres['destination_type'] ?? '') === $type->value)>Vers un {{ mb_strtolower($type->label()) }}</option>
            @endforeach
        </select>

        <label class="adm-filters__date">Du <input type="date" name="du" value="{{ $filtres['du'] ?? '' }}" class="adm-filters__select"></label>
        <label class="adm-filters__date">au <input type="date" name="au" value="{{ $filtres['au'] ?? '' }}" class="adm-filters__select"></label>

        <div class="adm-filters__actions">
            @if($filtresActifs)
                <a href="{{ route('admin.demandes.index') }}" class="adm-link">Réinitialiser</a>
            @endif
            <x-button type="submit" variant="outline" size="sm" icon="filter">Filtrer</x-button>
        </div>
    </form>
    @error('au')<p class="form-error">{{ $message }}</p>@enderror

    <div class="adm-card">
        <x-table :headers="['Demande', 'Demandeur', 'Destination', 'Statut', 'Soumise le', '']"
                 :empty="$demandes->isEmpty()"
                 empty-title="Aucune demande"
                 :empty-text="$filtresActifs ? 'Aucune demande ne correspond à ces critères.' : 'Aucune demande n\'a encore été soumise.'">
            @foreach($demandes as $demande)
                <tr>
                    <td>
                        <a href="{{ route('demandes.show', ['demande' => $demande, 'depuis' => 'admin']) }}" class="adm-user__name">{{ $demande->objet }}</a>
                        <span class="adm-cell-sub">{{ $demande->reference }}@if($demande->application) · {{ $demande->application->nom }}@endif</span>
                    </td>
                    <td>
                        <span class="adm-cell-main">{{ $demande->demandeur->nomComplet() }}</span>
                        <span class="adm-cell-sub">{{ $demande->serviceDemandeur?->libelle }}@if($demande->serviceDemandeur?->departement) · {{ $demande->serviceDemandeur->departement->libelle }}@endif</span>
                    </td>
                    <td>
                        <span class="adm-cell-main">{{ $demande->libelleDestination() }}</span>
                        @if($demande->receveur)<span class="adm-cell-sub">Receveur : {{ $demande->receveur->nomComplet() }}</span>@endif
                    </td>
                    <td>@include('demandes.partials.statut', ['statut' => $demande->statut])</td>
                    <td>
                        {{ $demande->soumise_le?->format('d/m/Y') }}
                        <span class="adm-cell-sub">mise à jour {{ $demande->updated_at->diffForHumans() }}</span>
                    </td>
                    <td class="adm-col-actions">
                        <a href="{{ route('demandes.show', ['demande' => $demande, 'depuis' => 'admin']) }}" class="adm-icon-btn" title="Voir {{ $demande->reference }}">
                            <x-icon name="chevron-right" size="sm" />
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-table>
        @include('admin.partials.pagination', ['pages' => $demandes])
    </div>
</div>
@endsection
