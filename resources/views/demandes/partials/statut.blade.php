{{-- Badge de statut d'une demande. Paramètres : $statut (DemandeStatut), $complet (bool, libellé long). --}}
<span class="dash-status {{ $statut->badgeClass() }}">{{ ($complet ?? false) ? $statut->libelleComplet() : $statut->label() }}</span>
