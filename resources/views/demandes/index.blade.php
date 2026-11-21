@extends('layouts.app')

@section('title', 'Mes demandes')
@section('page-title', 'Mes demandes')

@push('styles')
    @vite('resources/css/demandes/index.css')
@endpush

@section('content')

<div class="demandes-page" x-data="{ 
    createDrawerOpen: false, 
    viewDrawerOpen: false,
    demandeToView: null,
    deleteModalOpen: false, 
    demandeToDelete: null 
}">

    {{-- ===== EN-TÊTE ===== --}}
    <div class="demandes-header">
        <div>
            <h1 class="demandes-title">Mes demandes</h1>
            <p class="demandes-subtitle">Suivez l'avancement de vos demandes de ressources, du brouillon à la clôture.</p>
        </div>
        <button type="button" @click="createDrawerOpen = true" class="demandes-btn-primary">
            <x-icon name="plus" size="sm" />
            Nouvelle demande
        </button>
    </div>

    {{-- ===== COMPTEURS ===== --}}
    <div class="dash-kpis demandes-kpis">
        <x-kpi :value="$compteurs['total']" label="Total" hint="Toutes mes demandes" color="blue" icon="file-text" />
        <x-kpi :value="$compteurs['validation']" label="En validation" hint="Chez le N+1 ou le N+2" color="orange" icon="clock" />
        <x-kpi :value="$compteurs['action']" label="Action requise" hint="Infos à fournir ou réception à confirmer" color="violet" icon="alert-circle" />
        <x-kpi :value="$compteurs['cloturees']" label="Clôturées" hint="Réception confirmée" color="green" icon="check-circle" />
    </div>

    {{-- ===== FILTRES ===== --}}
    <form method="GET" action="{{ route('demandes.index') }}" class="demandes-filters">
        <div class="demandes-filters__search">
            <input type="search" name="q" value="{{ $query }}" placeholder="Rechercher par référence ou objet…" class="demandes-filters__input" aria-label="Rechercher">
        </div>
        <select name="statut" class="demandes-filters__select" aria-label="Filtrer par statut">
            <option value="">Tous les statuts</option>
            @foreach($statuts as $statut)
                <option value="{{ $statut->value }}" @selected($statutFiltre === $statut->value)>{{ $statut->label() }}</option>
            @endforeach
        </select>
        <div class="demandes-filters__actions">
            @if($query || $statutFiltre)
                <a href="{{ route('demandes.index') }}" class="demandes-filters__reset">Réinitialiser</a>
            @endif
            <button type="submit" class="demandes-filters__submit">Filtrer</button>
        </div>
    </form>

    {{-- ===== TABLEAU ===== --}}
    @if($demandes->isEmpty())
        <div class="demandes-empty">
            <div class="demandes-empty__icon">
                <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7m16 0v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5m16 0h-2.6a1 1 0 0 0-.7.3l-2.4 2.4a1 1 0 0 1-.7.3h-3.2a1 1 0 0 1-.7-.3l-2.4-2.4A1 1 0 0 0 6.6 13H4"/></svg>
            </div>
            <h3 class="demandes-empty__title">Aucune demande</h3>
            <p class="demandes-empty__text">Vous n'avez pas encore créé de demande.</p>
        </div>
    @else
        <div class="demandes-table-wrapper">
            <table class="demandes-table">
                <thead>
                    <tr>
                        <th>Réf.</th>
                        <th>Objet</th>
                        <th>Application</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Destination</th>
                        <th class="demandes-table__col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demandes as $demande)
                        <tr>
                            <td class="demandes-table__ref">{{ $demande->reference }}</td>
                            <td class="demandes-table__objet">{{ $demande->objet }}</td>
                            <td class="demandes-table__muted">{{ $demande->application ?? "—" }}</td>
                            <td>{{ $demande->date->format('d/m/Y') }}</td>
                            <td>
                                <span class="dash-status {{ $demande->statut_obj->badgeClass() }}">
                                    {{ $demande->statut_obj->label() }}
                                </span>
                            </td>
                            <td>{{ $demande->destination }}</td>
                            <td class="demandes-table__col-actions">
                                <div class="demandes-table__actions">
                                    
                                    {{-- Bouton Voir --}}
                                    <button type="button" class="demandes-table__action--btn view" title="Voir"
                                            @click="demandeToView = {
                                                reference: '{{ $demande->reference }}',
                                                objet: '{{ $demande->objet }}',
                                                application: '{{ $demande->application ?? "—" }}',
                                                date: '{{ $demande->date->format('d/m/Y') }}',
                                                statut: '{{ $demande->statut_obj->label() }}',
                                                destination: '{{ $demande->destination }}',
                                                badgeClass: '{{ $demande->statut_obj->badgeClass() }}'
                                            }; viewDrawerOpen = true;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>

                                    {{-- Boutons BROUILLON --}}
                                    @if($demande->statut_obj->value === 'BROUILLON')
                                        <button type="button" class="demandes-table__action--btn edit" title="Modifier">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        </button>
                                        
                                        <button type="button" class="demandes-table__action--btn submit" title="Soumettre">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                        </button>

                                        <button type="button" class="demandes-table__action--btn delete" title="Supprimer" 
                                                @click="demandeToDelete = '{{ $demande->reference }}'; deleteModalOpen = true;">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($demandes->hasPages())
            <div class="demandes-pagination">
                <div class="demandes-pagination__info">
                    Affichage <strong>{{ $demandes->firstItem() }}-{{ $demandes->lastItem() }}</strong>
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
                        <span class="demandes-pagination__btn demandes-pagination__btn--disabled">›</a>
                    @endif
                </div>
            </div>
        @endif
    @endif

    {{-- ===== DRAWER DE CRÉATION ===== --}}
    <template x-if="createDrawerOpen">
        <div class="drawer-overlay" @click.self="createDrawerOpen = false">
            <div class="drawer">
                <div class="drawer__header">
                    <h2 class="drawer__title">Nouvelle demande</h2>
                    <button class="drawer__close" @click="createDrawerOpen = false">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div class="form-group">
                        <label class="form-label form-label--required">Objet de la demande</label>
                        <input type="text" class="form-control" placeholder="Ex: Remplacement poste de travail">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Application concernée (Optionnel)</label>
                        <select class="form-control">
                            <option value="">-- Aucune --</option>
                            @foreach($applications as $app)
                                <option value="{{ $app }}">{{ $app }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label--required">Type de ressource</label>
                        <select class="form-control">
                            @foreach($ressources as $ress)
                                <option value="{{ $ress }}">{{ $ress }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quantité</label>
                        <input type="number" class="form-control" value="1" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label--required">Destination</label>
                        <select class="form-control">
                            @foreach($destinations as $dest)
                                <option value="{{ $dest }}">{{ $dest }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description / Motif</label>
                        <textarea class="form-control" rows="4" placeholder="Détails de la demande..."></textarea>
                    </div>
                </div>
                <div class="drawer__footer">
                    <button class="demandes-btn-export" @click="createDrawerOpen = false">Enregistrer en brouillon</button>
                    <button class="demandes-btn-primary">Soumettre</button>
                </div>
            </div>
        </div>
    </template>

    {{-- ===== DRAWER DE VISUALISATION ===== --}}
    <template x-if="viewDrawerOpen">
        <div class="drawer-overlay" @click.self="viewDrawerOpen = false">
            <div class="drawer">
                <div class="drawer__header">
                    <h2 class="drawer__title">Détails de la demande</h2>
                    <button class="drawer__close" @click="viewDrawerOpen = false">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="drawer__body">
                    <div class="form-group">
                        <label class="form-label">Référence</label>
                        <div style="font-family:monospace; font-weight:600; color:#27357e;" x-text="demandeToView?.reference"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Objet</label>
                        <div style="font-weight:500;" x-text="demandeToView?.objet"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Application</label>
                        <div x-text="demandeToView?.application"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date de création</label>
                        <div x-text="demandeToView?.date"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut actuel</label>
                        <div>
                            <span class="dash-status" :class="demandeToView?.badgeClass" x-text="demandeToView?.statut"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Destination</label>
                        <div x-text="demandeToView?.destination"></div>
                    </div>
                    
                    <hr style="border:0; border-top:1px solid #e2e5f0; margin:1rem 0;">
                    
                    <div class="form-group">
                        <label class="form-label">Description / Motif</label>
                        <div style="font-size:0.85rem; color:#6b7190; line-height:1.5;">
                            (Aperçu de la description de la demande...)
                        </div>
                    </div>
                </div>
                <div class="drawer__footer">
                    <button class="demandes-btn-export" @click="viewDrawerOpen = false">Fermer</button>
                </div>
            </div>
        </div>
    </template>

    {{-- ===== MODAL SUPPRESSION ===== --}}
    <template x-if="deleteModalOpen">
        <div class="modal-overlay" @click.self="deleteModalOpen = false">
            <div class="modal" style="max-width: 420px; text-align: center;">
                <div class="modal__body" style="padding-top: 2rem;">
                    <div style="width:64px; height:64px; border-radius:50%; background:#fadadf; color:#c8102e; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </div>
                    <h3 class="modal__title" style="margin-bottom:0.5rem;">Supprimer la demande ?</h3>
                    <p style="font-size:0.9rem; color:#6b7190; margin-bottom:1.5rem;">
                        Voulez-vous vraiment supprimer la demande <strong x-text="demandeToDelete"></strong> ? Cette action est irréversible.
                    </p>
                    <div style="display:flex; gap:0.75rem; justify-content:center;">
                        <button type="button" class="demandes-btn-export" @click="deleteModalOpen = false">Annuler</button>
                        <button type="button" class="demandes-btn-primary" style="background:#c8102e;" @click="deleteModalOpen = false">Supprimer</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>

@endsection