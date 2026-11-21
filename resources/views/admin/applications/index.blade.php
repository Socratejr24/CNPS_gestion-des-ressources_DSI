@extends('layouts.app')

@section('title', 'Applications')
@section('page-title', 'Applications')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@php($filtresActifs = collect($filtres)->filter(fn ($v) => filled($v))->isNotEmpty())

@section('content')
<div class="adm-page">

    <div class="adm-header">
        <div>
            <h1 class="adm-title">Applications</h1>
            <p class="adm-subtitle">
                Référentiel des applications concernées par les demandes de ressources (accès, comptes, licences…).
                Une application désactivée n'est plus proposée dans les nouvelles demandes, mais reste visible dans les anciennes.
            </p>
        </div>
        <x-button :href="route('admin.applications.create')" icon="plus">Nouvelle application</x-button>
    </div>

    <div class="adm-kpis">
        <div class="adm-kpi adm-kpi--static">
            <span class="adm-kpi__value">{{ $compteurs['total'] }}</span>
            <span class="adm-kpi__label">Applications</span>
        </div>
        <div class="adm-kpi adm-kpi--static">
            <span class="adm-kpi__value">{{ $compteurs['actives'] }}</span>
            <span class="adm-kpi__label">Actives</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.applications.index') }}" class="adm-filters">
        <input type="search" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Référence, nom ou description…"
               class="adm-filters__search" aria-label="Rechercher">
        <select name="statut" class="adm-filters__select" aria-label="Statut">
            <option value="">Tous statuts</option>
            <option value="actif" @selected(($filtres['statut'] ?? '') === 'actif')>Actives</option>
            <option value="inactif" @selected(($filtres['statut'] ?? '') === 'inactif')>Désactivées</option>
        </select>
        <div class="adm-filters__actions">
            @if($filtresActifs)
                <a href="{{ route('admin.applications.index') }}" class="adm-link">Réinitialiser</a>
            @endif
            <x-button type="submit" variant="outline" size="sm" icon="filter">Filtrer</x-button>
        </div>
    </form>

    <div class="adm-card">
        <x-table :headers="['Application', 'Référence', 'Créée le', 'Statut', '']"
                 :empty="$applications->isEmpty()"
                 empty-title="Aucune application"
                 :empty-text="$filtresActifs ? 'Aucune application ne correspond à ces filtres.' : 'Ajoutez les applications pour lesquelles des ressources peuvent être demandées.'">
            @foreach($applications as $application)
                <tr class="{{ $application->is_active ? '' : 'adm-row-off' }}">
                    <td>
                        <a href="{{ route('admin.applications.edit', $application) }}" class="adm-user__name">{{ $application->nom }}</a>
                        @if($application->description)<span class="adm-cell-sub">{{ \Illuminate\Support\Str::limit($application->description, 110) }}</span>@endif
                    </td>
                    <td><span class="org-code">{{ $application->reference }}</span></td>
                    <td>{{ $application->created_at?->format('d/m/Y') }}</td>
                    <td>
                        @if($application->is_active)
                            <x-badge state="validee" size="sm">Active</x-badge>
                        @else
                            <x-badge state="rejetee" size="sm">Désactivée</x-badge>
                        @endif
                    </td>
                    <td class="adm-col-actions">
                        <div class="org-actions">
                            <a href="{{ route('admin.applications.edit', $application) }}" class="adm-icon-btn" title="Modifier « {{ $application->nom }} »">
                                <x-icon name="edit" size="sm" />
                            </a>
                            @include('admin.organisation.partials.statut', [
                                'action' => route('admin.applications.statut', $application),
                                'actif' => $application->is_active, 'nom' => $application->nom,
                            ])
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
        @include('admin.partials.pagination', ['pages' => $applications])
    </div>
</div>
@endsection
