@extends('layouts.app')

@section('title', 'Utilisateurs')
@section('page-title', 'Utilisateurs')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@php
    use App\Enums\CategorieUtilisateur;
    $filtresActifs = collect($filtres)->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

@section('content')
<div class="adm-page">

    {{-- ===== EN-TÊTE ===== --}}
    <div class="adm-header">
        <div>
            <h1 class="adm-title">Utilisateurs</h1>
            <p class="adm-subtitle">
                Les agents CNPS sont créés à leur première connexion LDAP. Créez ici les comptes des prestataires,
                puis gérez l'affectation, les rôles et le statut de chacun.
            </p>
        </div>
        <x-button :href="route('admin.utilisateurs.create')" icon="plus">Nouveau prestataire</x-button>
    </div>

    {{-- ===== COMPTEURS ===== --}}
    <div class="adm-kpis">
        <a href="{{ route('admin.utilisateurs.index') }}" class="adm-kpi">
            <span class="adm-kpi__value">{{ $compteurs['total'] }}</span>
            <span class="adm-kpi__label">Utilisateurs</span>
        </a>
        <a href="{{ route('admin.utilisateurs.index', ['categorie' => 'AGENT']) }}" class="adm-kpi">
            <span class="adm-kpi__value">{{ $compteurs['agents'] }}</span>
            <span class="adm-kpi__label">Agents CNPS</span>
        </a>
        <a href="{{ route('admin.utilisateurs.index', ['categorie' => 'PRESTATAIRE']) }}" class="adm-kpi">
            <span class="adm-kpi__value">{{ $compteurs['prestataires'] }}</span>
            <span class="adm-kpi__label">Prestataires</span>
        </a>
        <a href="{{ route('admin.utilisateurs.index', ['affectation' => 'sans']) }}"
           class="adm-kpi {{ $compteurs['sans_affectation'] > 0 ? 'adm-kpi--alerte' : '' }}">
            <span class="adm-kpi__value">{{ $compteurs['sans_affectation'] }}</span>
            <span class="adm-kpi__label">Sans affectation</span>
        </a>
        <a href="{{ route('admin.utilisateurs.index', ['statut' => 'inactif']) }}" class="adm-kpi">
            <span class="adm-kpi__value">{{ $compteurs['inactifs'] }}</span>
            <span class="adm-kpi__label">Désactivés</span>
        </a>
    </div>

    {{-- ===== FILTRES ===== --}}
    <form method="GET" action="{{ route('admin.utilisateurs.index') }}" class="adm-filters">
        <input type="search" name="q" value="{{ $filtres['q'] ?? '' }}"
               placeholder="Nom, e-mail, matricule ou identifiant…"
               class="adm-filters__search" aria-label="Rechercher">

        <select name="categorie" class="adm-filters__select" aria-label="Catégorie">
            <option value="">Toutes catégories</option>
            @foreach(CategorieUtilisateur::cases() as $categorie)
                <option value="{{ $categorie->value }}" @selected(($filtres['categorie'] ?? '') === $categorie->value)>
                    {{ $categorie->label() }}
                </option>
            @endforeach
        </select>

        <select name="service_id" class="adm-filters__select" aria-label="Service">
            <option value="">Tous les services</option>
            @foreach($services as $groupe => $options)
                <optgroup label="{{ $groupe }}">
                    @foreach($options as $id => $libelle)
                        <option value="{{ $id }}" @selected((string) ($filtres['service_id'] ?? '') === (string) $id)>{{ $libelle }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>

        <select name="statut" class="adm-filters__select" aria-label="Statut">
            <option value="">Tous statuts</option>
            <option value="actif" @selected(($filtres['statut'] ?? '') === 'actif')>Actifs</option>
            <option value="inactif" @selected(($filtres['statut'] ?? '') === 'inactif')>Désactivés</option>
        </select>

        <label class="adm-filters__check">
            <input type="checkbox" name="affectation" value="sans" @checked(($filtres['affectation'] ?? '') === 'sans')>
            Sans affectation
        </label>

        <div class="adm-filters__actions">
            @if($filtresActifs)
                <a href="{{ route('admin.utilisateurs.index') }}" class="adm-link">Réinitialiser</a>
            @endif
            <x-button type="submit" variant="outline" size="sm" icon="filter">Filtrer</x-button>
        </div>
    </form>

    {{-- ===== TABLEAU ===== --}}
    <div class="adm-card">
        <x-table :headers="['Utilisateur', 'Catégorie', 'Affectation', 'Rôles', 'Statut', '']"
                 :empty="$utilisateurs->isEmpty()"
                 empty-title="Aucun utilisateur"
                 :empty-text="$filtresActifs ? 'Aucun utilisateur ne correspond à ces filtres.' : 'Aucun utilisateur pour le moment.'">
            @foreach($utilisateurs as $u)
                <tr>
                    <td>
                        <div class="adm-user">
                            <span class="adm-avatar">{{ mb_strtoupper(mb_substr($u->prenom, 0, 1).mb_substr($u->nom, 0, 1)) }}</span>
                            <div>
                                <a href="{{ route('admin.utilisateurs.show', $u) }}" class="adm-user__name">{{ $u->nomComplet() }}</a>
                                <span class="adm-user__meta">{{ $u->email }}@if($u->matricule) · {{ $u->matricule }}@endif</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="adm-tag {{ $u->categorie === CategorieUtilisateur::AGENT ? 'adm-tag--agent' : 'adm-tag--presta' }}">
                            {{ $u->categorie->label() }}
                        </span>
                    </td>
                    <td>
                        @if($u->service)
                            <span class="adm-cell-main">{{ $u->service->libelle }}</span>
                            <span class="adm-cell-sub">{{ $u->service->departement?->libelle }}</span>
                        @else
                            <x-badge state="attente" size="sm">En attente</x-badge>
                        @endif
                    </td>
                    <td>
                        <div class="adm-roles">
                            @forelse($u->roles->where('is_active', true) as $role)
                                <span class="adm-role">{{ $role->libelle }}</span>
                            @empty
                                <span class="adm-cell-sub">Aucun</span>
                            @endforelse
                        </div>
                    </td>
                    <td>
                        @if($u->is_active)
                            <x-badge state="validee" size="sm">Actif</x-badge>
                        @else
                            <x-badge state="rejetee" size="sm">Désactivé</x-badge>
                        @endif
                    </td>
                    <td class="adm-col-actions">
                        <a href="{{ route('admin.utilisateurs.show', $u) }}" class="adm-icon-btn" title="Gérer {{ $u->nomComplet() }}">
                            <x-icon name="chevron-right" size="sm" />
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-table>

        @if($utilisateurs->hasPages())
            <div class="adm-pagination">
                <span>
                    <strong>{{ $utilisateurs->firstItem() }}–{{ $utilisateurs->lastItem() }}</strong>
                    sur <strong>{{ $utilisateurs->total() }}</strong>
                </span>
                <div class="adm-pagination__nav">
                    @if($utilisateurs->onFirstPage())
                        <span class="adm-icon-btn adm-icon-btn--off"><x-icon name="chevron-left" size="sm" /></span>
                    @else
                        <a href="{{ $utilisateurs->previousPageUrl() }}" class="adm-icon-btn" title="Page précédente"><x-icon name="chevron-left" size="sm" /></a>
                    @endif
                    <span>Page {{ $utilisateurs->currentPage() }} / {{ $utilisateurs->lastPage() }}</span>
                    @if($utilisateurs->hasMorePages())
                        <a href="{{ $utilisateurs->nextPageUrl() }}" class="adm-icon-btn" title="Page suivante"><x-icon name="chevron-right" size="sm" /></a>
                    @else
                        <span class="adm-icon-btn adm-icon-btn--off"><x-icon name="chevron-right" size="sm" /></span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
