@extends('layouts.app')

@section('title', 'Mes demandes')
@section('page-title', 'Mes demandes')

@push('styles')
    @vite('resources/css/demandes/index.css')
@endpush

@php($filtresActifs = collect($filtres)->filter(fn ($v) => filled($v))->isNotEmpty())

@section('content')
<div class="demandes-page">

    {{-- ===== EN-TÊTE ===== --}}
    <div class="demandes-header">
        <div>
            <h1 class="demandes-title">Mes demandes</h1>
            <p class="demandes-subtitle">Suivez l'avancement de vos demandes de ressources, du brouillon à la clôture.</p>
        </div>
        <a href="{{ route('demandes.create') }}" class="demandes-btn-primary">
            <x-icon name="plus" size="sm" />
            Nouvelle demande
        </a>
    </div>

    {{-- ===== COMPTEURS ===== --}}
    <div class="dash-kpis demandes-kpis">
        <x-kpi :value="$compteurs['total']" label="Total" :hint="$compteurs['brouillons'].' brouillon(s)'" color="blue" icon="file-text" />
        <x-kpi :value="$compteurs['validation']" label="En validation" hint="Chez le N+1 ou le N+2" color="orange" icon="clock" />
        <x-kpi :value="$compteurs['action']" label="Action requise" hint="Infos à fournir ou réception à confirmer" color="violet" icon="alert-circle" />
        <x-kpi :value="$compteurs['cloturees']" label="Clôturées" hint="Réception confirmée" color="green" icon="check-circle" />
    </div>

    {{-- ===== FILTRES ===== --}}
    <form method="GET" action="{{ route('demandes.index') }}" class="demandes-filters">
        <div class="demandes-filters__search">
            <input type="search" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Rechercher par référence ou objet…"
                   class="demandes-filters__input" aria-label="Rechercher">
        </div>
        <select name="statut" class="demandes-filters__select" aria-label="Filtrer par statut">
            <option value="">Tous les statuts</option>
            @foreach($statuts as $statut)
                <option value="{{ $statut->value }}" @selected(($filtres['statut'] ?? '') === $statut->value)>{{ $statut->label() }}</option>
            @endforeach
        </select>
        <div class="demandes-filters__actions">
            @if($filtresActifs)
                <a href="{{ route('demandes.index') }}" class="demandes-filters__reset">Réinitialiser</a>
            @endif
            <button type="submit" class="demandes-filters__submit">Filtrer</button>
        </div>
    </form>

    {{-- ===== TABLEAU ===== --}}
    @if($demandes->isEmpty())
        <div class="demandes-empty">
            <div class="demandes-empty__icon"><x-icon name="inbox" size="xl" /></div>
            <h3 class="demandes-empty__title">{{ $filtresActifs ? 'Aucune demande trouvée' : 'Aucune demande' }}</h3>
            <p class="demandes-empty__text">
                {{ $filtresActifs ? 'Aucune demande ne correspond à ces critères.' : 'Vous n\'avez pas encore créé de demande.' }}
            </p>
            @unless($filtresActifs)
                <a href="{{ route('demandes.create') }}" class="demandes-btn-primary"><x-icon name="plus" size="sm" /> Créer ma première demande</a>
            @endunless
        </div>
    @else
        <div class="demandes-table-wrapper">
            <table class="demandes-table">
                <thead>
                    <tr>
                        <th>Réf.</th>
                        <th>Objet</th>
                        <th>Application</th>
                        <th>Mise à jour</th>
                        <th>Statut</th>
                        <th>Destination</th>
                        <th class="demandes-table__col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demandes as $demande)
                        <tr>
                            <td class="demandes-table__ref">
                                <a href="{{ route('demandes.show', $demande) }}">{{ $demande->reference }}</a>
                            </td>
                            <td class="demandes-table__objet">{{ $demande->objet }}</td>
                            <td class="demandes-table__muted">{{ $demande->application?->nom ?? '—' }}</td>
                            <td>{{ $demande->updated_at->format('d/m/Y') }}</td>
                            <td>@include('demandes.partials.statut', ['statut' => $demande->statut])</td>
                            <td class="demandes-table__muted">{{ $demande->destination_type ? $demande->libelleDestination() : '—' }}</td>
                            <td class="demandes-table__col-actions">
                                <div class="demandes-table__actions">
                                    <a href="{{ route('demandes.show', $demande) }}" class="demandes-table__action--btn view" title="Voir {{ $demande->reference }}">
                                        <x-icon name="eye" size="sm" />
                                    </a>
                                    @if($demande->estBrouillon())
                                        <a href="{{ route('demandes.edit', $demande) }}" class="demandes-table__action--btn edit" title="Modifier le brouillon">
                                            <x-icon name="edit" size="sm" />
                                        </a>
                                        <form method="POST" action="{{ route('demandes.soumettre', $demande) }}"
                                              onsubmit="return confirm(@js('Soumettre la demande '.$demande->reference.' ? Elle ne sera plus modifiable.'))">
                                            @csrf
                                            <button type="submit" class="demandes-table__action--btn submit" title="Soumettre">
                                                <x-icon name="arrow-right" size="sm" />
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('demandes.destroy', $demande) }}"
                                              onsubmit="return confirm(@js('Supprimer définitivement le brouillon '.$demande->reference.' ?'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="demandes-table__action--btn delete" title="Supprimer le brouillon">
                                                <x-icon name="trash" size="sm" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($demandes->hasPages())
            <div class="demandes-pagination">
                <div class="demandes-pagination__info">
                    Affichage <strong>{{ $demandes->firstItem() }}–{{ $demandes->lastItem() }}</strong>
                    sur <strong>{{ $demandes->total() }}</strong>
                </div>
                <div class="demandes-pagination__nav">
                    @if($demandes->onFirstPage())
                        <span class="demandes-pagination__btn demandes-pagination__btn--disabled">‹</span>
                    @else
                        <a href="{{ $demandes->previousPageUrl() }}" class="demandes-pagination__btn">‹</a>
                    @endif
                    @foreach($demandes->getUrlRange(max(1, $demandes->currentPage() - 2), min($demandes->lastPage(), $demandes->currentPage() + 2)) as $page => $url)
                        @if($page == $demandes->currentPage())
                            <span class="demandes-pagination__btn demandes-pagination__btn--active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="demandes-pagination__btn">{{ $page }}</a>
                        @endif
                    @endforeach
                    @if($demandes->hasMorePages())
                        <a href="{{ $demandes->nextPageUrl() }}" class="demandes-pagination__btn">›</a>
                    @else
                        <span class="demandes-pagination__btn demandes-pagination__btn--disabled">›</span>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
