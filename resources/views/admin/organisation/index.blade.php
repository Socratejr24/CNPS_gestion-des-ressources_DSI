@extends('layouts.app')

@section('title', 'Organisation')
@section('page-title', 'Organisation')

@push('styles')
    @vite('resources/css/admin/index.css')
@endpush

@section('content')
<div class="adm-page">

    {{-- ===== EN-TÊTE ===== --}}
    <div class="adm-header">
        <div>
            <h1 class="adm-title">Organisation de la DSI</h1>
            <p class="adm-subtitle">
                Structures, départements et services. Le responsable de département (N+2) et le chef de service (N+1)
                forment le circuit de validation des demandes. Une entité ne se supprime pas : elle se désactive.
            </p>
        </div>
        <x-button :href="route('admin.organisation.structures.create')" icon="plus">Nouvelle structure</x-button>
    </div>

    @error('regle_metier')
        <x-alert type="error" title="Action impossible" closable>{{ $message }}</x-alert>
    @enderror

    {{-- ===== COMPTEURS ===== --}}
    <div class="adm-kpis">
        <div class="adm-kpi adm-kpi--static">
            <span class="adm-kpi__value">{{ $compteurs['structures'] }}</span>
            <span class="adm-kpi__label">Structures actives</span>
        </div>
        <div class="adm-kpi adm-kpi--static">
            <span class="adm-kpi__value">{{ $compteurs['departements'] }}</span>
            <span class="adm-kpi__label">Départements actifs</span>
        </div>
        <div class="adm-kpi adm-kpi--static">
            <span class="adm-kpi__value">{{ $compteurs['services'] }}</span>
            <span class="adm-kpi__label">Services actifs</span>
        </div>
        <div class="adm-kpi adm-kpi--static {{ $compteurs['sans_responsable'] ? 'adm-kpi--alerte' : '' }}">
            <span class="adm-kpi__value">{{ $compteurs['sans_responsable'] }}</span>
            <span class="adm-kpi__label">Départements sans N+2</span>
        </div>
        <div class="adm-kpi adm-kpi--static {{ $compteurs['sans_chef'] ? 'adm-kpi--alerte' : '' }}">
            <span class="adm-kpi__value">{{ $compteurs['sans_chef'] }}</span>
            <span class="adm-kpi__label">Services sans N+1</span>
        </div>
        <a href="{{ route('admin.utilisateurs.index', ['affectation' => 'sans', 'statut' => 'actif']) }}"
           class="adm-kpi {{ $compteurs['sans_affectation'] ? 'adm-kpi--alerte' : '' }}">
            <span class="adm-kpi__value">{{ $compteurs['sans_affectation'] }}</span>
            <span class="adm-kpi__label">Utilisateurs à affecter</span>
        </a>
    </div>

    {{-- ===== ARBORESCENCE ===== --}}
    @forelse($structures as $structure)
        <section class="adm-card org-structure {{ $structure->is_active ? '' : 'org-off' }}">
            <header class="org-structure__header">
                <div class="org-title">
                    <x-icon name="building" size="md" class="org-title__icon" />
                    <div>
                        <h2 class="org-title__name">
                            {{ $structure->libelle }}
                            <span class="org-code">{{ $structure->code }}</span>
                            @unless($structure->is_active)<x-badge state="rejetee" size="sm">Désactivée</x-badge>@endunless
                        </h2>
                        @if($structure->description)<p class="org-desc">{{ $structure->description }}</p>@endif
                    </div>
                </div>
                <div class="org-actions">
                    @if($structure->is_active)
                        <x-button :href="route('admin.organisation.departements.create', ['structure_id' => $structure->id])"
                                  variant="outline" size="sm" icon="plus">Département</x-button>
                    @endif
                    <a href="{{ route('admin.organisation.structures.edit', $structure) }}" class="adm-icon-btn" title="Modifier « {{ $structure->libelle }} »">
                        <x-icon name="edit" size="sm" />
                    </a>
                    @include('admin.organisation.partials.statut', [
                        'action' => route('admin.organisation.structures.statut', $structure),
                        'actif' => $structure->is_active, 'nom' => $structure->libelle,
                    ])
                </div>
            </header>

            <div class="org-structure__body">
                @forelse($structure->departements as $departement)
                    <div class="org-dept {{ $departement->is_active ? '' : 'org-off' }}">
                        <div class="org-dept__header">
                            <div class="org-title">
                                <x-icon name="layers" size="sm" class="org-title__icon" />
                                <div>
                                    <h3 class="org-dept__name">
                                        {{ $departement->libelle }}
                                        <span class="org-code">{{ $departement->code }}</span>
                                        @unless($departement->is_active)<x-badge state="rejetee" size="sm">Désactivé</x-badge>@endunless
                                    </h3>
                                    <span class="org-person">
                                        N+2 :
                                        @if($departement->responsable)
                                            <a href="{{ route('admin.utilisateurs.show', $departement->responsable) }}">{{ $departement->responsable->nomComplet() }}</a>
                                        @else
                                            <span class="org-missing">aucun responsable désigné</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="org-actions">
                                @if($departement->is_active)
                                    <x-button :href="route('admin.organisation.services.create', ['departement_id' => $departement->id])"
                                              variant="ghost" size="sm" icon="plus">Service</x-button>
                                @endif
                                <a href="{{ route('admin.organisation.departements.edit', $departement) }}" class="adm-icon-btn" title="Modifier « {{ $departement->libelle }} »">
                                    <x-icon name="edit" size="sm" />
                                </a>
                                @include('admin.organisation.partials.statut', [
                                    'action' => route('admin.organisation.departements.statut', $departement),
                                    'actif' => $departement->is_active, 'nom' => $departement->libelle,
                                ])
                            </div>
                        </div>

                        @if($departement->services->isNotEmpty())
                            <ul class="org-services">
                                @foreach($departement->services as $service)
                                    <li class="org-service {{ $service->is_active ? '' : 'org-off' }}">
                                        <div class="org-service__main">
                                            <span class="org-service__name">
                                                {{ $service->libelle }}
                                                <span class="org-code">{{ $service->code }}</span>
                                                @unless($service->is_active)<x-badge state="rejetee" size="sm">Désactivé</x-badge>@endunless
                                            </span>
                                            <span class="org-person">
                                                N+1 :
                                                @if($service->chef)
                                                    <a href="{{ route('admin.utilisateurs.show', $service->chef) }}">{{ $service->chef->nomComplet() }}</a>
                                                @else
                                                    <span class="org-missing">aucun chef désigné</span>
                                                @endif
                                            </span>
                                        </div>
                                        <a href="{{ route('admin.utilisateurs.index', ['service_id' => $service->id]) }}" class="org-count"
                                           title="Voir les utilisateurs du service">
                                            <x-icon name="users" size="xs" /> {{ $service->utilisateurs_count }}
                                        </a>
                                        <div class="org-actions">
                                            <a href="{{ route('admin.organisation.services.edit', $service) }}" class="adm-icon-btn" title="Modifier « {{ $service->libelle }} »">
                                                <x-icon name="edit" size="sm" />
                                            </a>
                                            @include('admin.organisation.partials.statut', [
                                                'action' => route('admin.organisation.services.statut', $service),
                                                'actif' => $service->is_active, 'nom' => $service->libelle,
                                            ])
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="org-empty">Aucun service dans ce département.</p>
                        @endif
                    </div>
                @empty
                    <p class="org-empty">Aucun département dans cette structure.</p>
                @endforelse
            </div>
        </section>
    @empty
        <div class="adm-card adm-card__body org-empty-state">
            <x-icon name="building" size="xl" />
            <h3>Aucune structure</h3>
            <p>Commencez par créer une structure (par exemple la DSI), puis ses départements et ses services.</p>
            <x-button :href="route('admin.organisation.structures.create')" icon="plus">Créer une structure</x-button>
        </div>
    @endforelse
</div>
@endsection
