<?php

namespace App\Services\Demandes;

use App\Enums\ActionDemande;
use App\Enums\DemandeStatut;
use App\Models\Demande;
use App\Models\DemandeEvenement;
use App\Models\User;

/**
 * Écriture de l'historique d'une demande (SFD §13.2, RG-33).
 * Seul point d'écriture de demande_evenements.
 */
final class Journal
{
    public static function ajouter(
        Demande $demande,
        ?User $auteur,
        ActionDemande $action,
        ?DemandeStatut $ancien,
        ?DemandeStatut $nouveau,
        ?string $commentaire = null,
        ?string $niveau = null,
    ): DemandeEvenement {
        return $demande->evenements()->create([
            'utilisateur_id' => $auteur?->id,
            'action' => $action,
            'ancien_statut' => $ancien,
            'nouveau_statut' => $nouveau,
            'niveau' => $niveau,
            'commentaire' => $commentaire,
        ]);
    }
}
