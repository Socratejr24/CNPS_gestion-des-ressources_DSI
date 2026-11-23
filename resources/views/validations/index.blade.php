@extends('layouts.app')

@section('title', 'Demandes à valider')
@section('page-title', 'Demandes à valider')

@push('styles')
    @vite(['resources/css/admin/index.css', 'resources/css/demandes/index.css'])
@endpush

@section('content')
<div class="adm-page">

    <div class="adm-header">
        <div>
            <h1 class="adm-title">Demandes à valider</h1>
            <p class="adm-subtitle">
                Demandes de vos collaborateurs qui attendent votre décision, en tant que chef de service (N+1)
                ou responsable de département (N+2). Les plus anciennes apparaissent en premier.
            </p>
        </div>
    </div>

    <div class="adm-tabs" role="tablist">
        <a href="{{ route('validations.index') }}" role="tab" class="adm-tab {{ $onglet === 'a-valider' ? 'adm-tab--active' : '' }}">
            <x-icon name="clock" size="sm" /> À valider <span class="adm-tab__count">{{ $nombreAValider }}</span>
        </a>
        <a href="{{ route('validations.index', ['onglet' => 'traitees']) }}" role="tab" class="adm-tab {{ $onglet === 'traitees' ? 'adm-tab--active' : '' }}">
            <x-icon name="check-circle" size="sm" /> Traitées par moi
        </a>
    </div>

    <form method="GET" action="{{ route('validations.index') }}" class="adm-filters">
        @if($onglet === 'traitees')<input type="hidden" name="onglet" value="traitees">@endif
        <input type="search" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Référence, objet ou nom du demandeur…"
               class="adm-filters__search" aria-label="Rechercher">
        <div class="adm-filters__actions">
            @if(filled($filtres['q'] ?? null))
                <a href="{{ route('validations.index', $onglet === 'traitees' ? ['onglet' => 'traitees'] : []) }}" class="adm-link">Réinitialiser</a>
            @endif
            <x-button type="submit" variant="outline" size="sm" icon="search">Rechercher</x-button>
        </div>
    </form>

    <div class="adm-card">
        <x-table :headers="['Demande', 'Demandeur', $onglet === 'traitees' ? 'Statut actuel' : 'Étape', 'Soumise le', '']"
                 :empty="$demandes->isEmpty()"
                 :empty-title="$onglet === 'traitees' ? 'Aucune demande traitée' : 'Aucune demande en attente'"
                 :empty-text="$onglet === 'traitees' ? 'Les demandes sur lesquelles vous avez statué apparaîtront ici.' : 'Vous êtes à jour : aucune demande n\'attend votre décision.'">
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
                    <td>@include('demandes.partials.statut', ['statut' => $demande->statut])</td>
                    <td>
                        {{ $demande->soumise_le?->format('d/m/Y') }}
                        @if($onglet === 'a-valider' && $demande->soumise_le)
                            <span class="adm-cell-sub">{{ $demande->soumise_le->diffForHumans() }}</span>
                        @endif
                    </td>
                    <td class="adm-col-actions">
                        <x-button :href="route('demandes.show', $demande)" size="sm" :variant="$onglet === 'a-valider' ? 'primary' : 'ghost'">
                            {{ $onglet === 'a-valider' ? 'Examiner' : 'Voir' }}
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
        @include('admin.partials.pagination', ['pages' => $demandes])
    </div>
</div>
@endsection
