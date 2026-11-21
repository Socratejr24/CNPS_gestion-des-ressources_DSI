@extends('layouts.app')

@section('title', 'Ressources')
@section('page-title', 'Ressources')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@php($filtresActifs = collect($filtres)->filter(fn ($v) => filled($v))->isNotEmpty())

@section('content')
<div class="adm-page">

    <div class="adm-header">
        <div>
            <h1 class="adm-title">Ressources</h1>
            <p class="adm-subtitle">
                Catalogue des ressources que les utilisateurs peuvent demander (matériel, logiciels, accès…).
                Une ressource désactivée n'est plus proposée dans les nouvelles demandes.
            </p>
        </div>
        <x-button :href="route('admin.ressources.create')" icon="plus">Nouvelle ressource</x-button>
    </div>

    <div class="adm-kpis">
        <div class="adm-kpi adm-kpi--static">
            <span class="adm-kpi__value">{{ $compteurs['total'] }}</span>
            <span class="adm-kpi__label">Ressources</span>
        </div>
        <div class="adm-kpi adm-kpi--static">
            <span class="adm-kpi__value">{{ $compteurs['actives'] }}</span>
            <span class="adm-kpi__label">Actives</span>
        </div>
        <div class="adm-kpi adm-kpi--static">
            <span class="adm-kpi__value">{{ $compteurs['categories'] }}</span>
            <span class="adm-kpi__label">Catégories</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.ressources.index') }}" class="adm-filters">
        <input type="search" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Référence, désignation ou description…"
               class="adm-filters__search" aria-label="Rechercher">
        <select name="categorie" class="adm-filters__select" aria-label="Catégorie">
            <option value="">Toutes catégories</option>
            @foreach($categories as $categorie)
                <option value="{{ $categorie }}" @selected(($filtres['categorie'] ?? '') === $categorie)>{{ $categorie }}</option>
            @endforeach
        </select>
        <select name="statut" class="adm-filters__select" aria-label="Statut">
            <option value="">Tous statuts</option>
            <option value="actif" @selected(($filtres['statut'] ?? '') === 'actif')>Actives</option>
            <option value="inactif" @selected(($filtres['statut'] ?? '') === 'inactif')>Désactivées</option>
        </select>
        <div class="adm-filters__actions">
            @if($filtresActifs)
                <a href="{{ route('admin.ressources.index') }}" class="adm-link">Réinitialiser</a>
            @endif
            <x-button type="submit" variant="outline" size="sm" icon="filter">Filtrer</x-button>
        </div>
    </form>

    <div class="adm-card">
        <x-table :headers="['Ressource', 'Catégorie', 'Référence', 'Statut', '']"
                 :empty="$ressources->isEmpty()"
                 empty-title="Aucune ressource"
                 :empty-text="$filtresActifs ? 'Aucune ressource ne correspond à ces filtres.' : 'Ajoutez les ressources que les utilisateurs pourront demander.'">
            @foreach($ressources as $ressource)
                <tr class="{{ $ressource->is_active ? '' : 'adm-row-off' }}">
                    <td>
                        <a href="{{ route('admin.ressources.edit', $ressource) }}" class="adm-user__name">{{ $ressource->designation }}</a>
                        @if($ressource->description)<span class="adm-cell-sub">{{ \Illuminate\Support\Str::limit($ressource->description, 110) }}</span>@endif
                    </td>
                    <td><span class="adm-role">{{ $ressource->categorie }}</span></td>
                    <td><span class="org-code">{{ $ressource->reference }}</span></td>
                    <td>
                        @if($ressource->is_active)
                            <x-badge state="validee" size="sm">Active</x-badge>
                        @else
                            <x-badge state="rejetee" size="sm">Désactivée</x-badge>
                        @endif
                    </td>
                    <td class="adm-col-actions">
                        <div class="org-actions">
                            <a href="{{ route('admin.ressources.edit', $ressource) }}" class="adm-icon-btn" title="Modifier « {{ $ressource->designation }} »">
                                <x-icon name="edit" size="sm" />
                            </a>
                            @include('admin.organisation.partials.statut', [
                                'action' => route('admin.ressources.statut', $ressource),
                                'actif' => $ressource->is_active, 'nom' => $ressource->designation,
                            ])
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
        @include('admin.partials.pagination', ['pages' => $ressources])
    </div>
</div>
@endsection
