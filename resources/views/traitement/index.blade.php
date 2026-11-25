@extends('layouts.app')

@section('title', 'Demandes reçues')
@section('page-title', 'Demandes reçues')

@push('styles')
    @vite(['resources/css/admin/index.css', 'resources/css/demandes/index.css'])
@endpush

@php
    $titresVides = [
        'a-prendre' => ['Aucune demande à prendre en charge', 'Les demandes validées et adressées à votre périmètre apparaîtront ici.'],
        'en-cours' => ['Aucun traitement en cours', 'Les demandes que vous avez prises en charge apparaîtront ici jusqu\'à leur clôture.'],
        'terminees' => ['Aucune demande terminée', 'Les demandes clôturées ou impossibles à traiter apparaîtront ici.'],
    ];
@endphp

@section('content')
<div class="adm-page">

    <div class="adm-header">
        <div>
            <h1 class="adm-title">Demandes reçues</h1>
            <p class="adm-subtitle">
                Demandes validées par la hiérarchie et adressées à votre périmètre (vous-même, votre service ou votre département).
                Prenez-en charge une demande pour la traiter.
            </p>
        </div>
    </div>

    <div class="adm-tabs" role="tablist">
        <a href="{{ route('traitement.index') }}" role="tab" class="adm-tab {{ $onglet === 'a-prendre' ? 'adm-tab--active' : '' }}">
            <x-icon name="inbox" size="sm" /> À prendre en charge <span class="adm-tab__count">{{ $compteurs['a-prendre'] }}</span>
        </a>
        <a href="{{ route('traitement.index', ['onglet' => 'en-cours']) }}" role="tab" class="adm-tab {{ $onglet === 'en-cours' ? 'adm-tab--active' : '' }}">
            <x-icon name="clock" size="sm" /> Mes traitements en cours <span class="adm-tab__count">{{ $compteurs['en-cours'] }}</span>
        </a>
        <a href="{{ route('traitement.index', ['onglet' => 'terminees']) }}" role="tab" class="adm-tab {{ $onglet === 'terminees' ? 'adm-tab--active' : '' }}">
            <x-icon name="check-circle" size="sm" /> Terminées
        </a>
    </div>

    <form method="GET" action="{{ route('traitement.index') }}" class="adm-filters">
        @if($onglet !== 'a-prendre')<input type="hidden" name="onglet" value="{{ $onglet }}">@endif
        <input type="search" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Référence, objet ou nom du demandeur…"
               class="adm-filters__search" aria-label="Rechercher">
        <div class="adm-filters__actions">
            @if(filled($filtres['q'] ?? null))
                <a href="{{ route('traitement.index', $onglet !== 'a-prendre' ? ['onglet' => $onglet] : []) }}" class="adm-link">Réinitialiser</a>
            @endif
            <x-button type="submit" variant="outline" size="sm" icon="search">Rechercher</x-button>
        </div>
    </form>

    <div class="adm-card">
        <x-table :headers="['Demande', 'Demandeur', 'Destination', 'Statut', $onglet === 'a-prendre' ? 'Transmise le' : 'Prise en charge', '']"
                 :empty="$demandes->isEmpty()" :empty-title="$titresVides[$onglet][0]" :empty-text="$titresVides[$onglet][1]">
            @foreach($demandes as $demande)
                <tr>
                    <td>
                        <a href="{{ route('demandes.show', $demande) }}" class="adm-user__name">{{ $demande->objet }}</a>
                        <span class="adm-cell-sub">{{ $demande->reference }}@if($demande->application) · {{ $demande->application->nom }}@endif</span>
                    </td>
                    <td>
                        <span class="adm-cell-main">{{ $demande->demandeur->nomComplet() }}</span>
                        <span class="adm-cell-sub">{{ $demande->serviceDemandeur?->libelle }}</span>
                    </td>
                    <td class="adm-cell-sub">{{ $demande->libelleDestination() }}</td>
                    <td>@include('demandes.partials.statut', ['statut' => $demande->statut])</td>
                    <td>
                        @if($onglet === 'a-prendre')
                            {{ $demande->updated_at->format('d/m/Y') }}
                            <span class="adm-cell-sub">{{ $demande->updated_at->diffForHumans() }}</span>
                        @else
                            {{ $demande->prise_en_charge_le?->format('d/m/Y') ?? '—' }}
                        @endif
                    </td>
                    <td class="adm-col-actions">
                        <x-button :href="route('demandes.show', $demande)" size="sm" :variant="$onglet === 'terminees' ? 'ghost' : 'primary'">
                            {{ $onglet === 'terminees' ? 'Voir' : 'Ouvrir' }}
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
        @include('admin.partials.pagination', ['pages' => $demandes])
    </div>
</div>
@endsection
