@php
    use App\Enums\DemandeStatut;

    // Couleur de l'icône selon l'étape du cycle de vie
    $couleur = fn (DemandeStatut $s) => match ($s) {
        DemandeStatut::BROUILLON => 'gray',
        DemandeStatut::EN_ATTENTE_N1, DemandeStatut::EN_ATTENTE_N2,
        DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE, DemandeStatut::EN_COURS_REGULARISATION => 'orange',
        DemandeStatut::EN_ATTENTE_TRAITEMENT, DemandeStatut::EN_COURS_TRAITEMENT => 'blue',
        DemandeStatut::EN_ATTENTE_CONFIRMATION => 'violet',
        DemandeStatut::CLOTUREE => 'green',
        DemandeStatut::REJETEE, DemandeStatut::TRAITEMENT_IMPOSSIBLE => 'red',
    };
@endphp

<div class="dash-block">
    <div class="dash-block__header">
        <h3 class="dash-block__title">
            <x-icon name="file-text" size="sm" />
            {{ $global ? 'Dernières demandes' : 'Mes dernières demandes' }}
        </h3>
        @unless($global)
            <a href="{{ route('demandes.index') }}" class="dash-block__view-all">Tout voir</a>
        @endunless
    </div>

    <div class="dash-block__body">
        @forelse($recentesDemandes as $demande)
            <a href="{{ route('demandes.show', $demande) }}" class="dash-item dash-item--lien">
                <div class="dash-item__icon dash-item__icon--{{ $couleur($demande->statut) }}">
                    <x-icon name="file" size="sm" />
                </div>
                <div class="dash-item__content">
                    <p class="dash-item__title">{{ $demande->objet }}</p>
                    <div class="dash-item__meta">
                        <span>{{ $demande->reference }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ $demande->updated_at->format('d/m/Y') }}</span>
                    </div>
                </div>
                <span class="dash-status {{ $demande->statut->badgeClass() }}">
                    {{ $demande->statut->label() }}
                </span>
            </a>
        @empty
            <div class="dash-empty">
                <p class="dash-empty__text">
                    {{ $global ? 'Aucune demande soumise pour le moment.' : 'Vous n\'avez encore créé aucune demande.' }}
                </p>
            </div>
        @endforelse
    </div>
</div>
