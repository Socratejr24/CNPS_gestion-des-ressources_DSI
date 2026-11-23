<?php

namespace App\Services\Demandes;

use App\Enums\ActionDemande;
use App\Enums\DemandeStatut;
use App\Exceptions\RegleMetierException;
use App\Models\Demande;
use App\Models\User;

/**
 * Demandes d'informations complémentaires (SFD §10, RG-24 à RG-26).
 *
 * Ouverture par un valideur (N+1 / N+2) ou un receveur ; la demande passe en
 * INFO_COMPLEMENTAIRE_DEMANDEE en mémorisant l'étape à reprendre. Après la réponse
 * du demandeur, elle revient à cette étape, sans refaire les validations déjà faites.
 * À appeler dans une transaction.
 */
final class Complements
{
    public static function ouvrir(Demande $demande, User $auteur, string $question, ?string $niveau = null): void
    {
        if (blank($question)) {
            throw new RegleMetierException('Précisez les informations attendues du demandeur.');
        }

        $ancien = $demande->statut;
        $demande->statut_retour_complement = $ancien;
        $demande->complement_demande_par_id = $auteur->id;
        $demande->statut = DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE;
        $demande->save();

        Journal::ajouter($demande, $auteur, ActionDemande::DEMANDE_INFORMATIONS, $ancien, $demande->statut, trim($question), $niveau);
    }

    public static function repondre(Demande $demande, User $demandeur, string $reponse): void
    {
        if ($demande->statut !== DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE || $demande->statut_retour_complement === null) {
            throw new RegleMetierException('Aucune information complémentaire n\'est attendue sur cette demande.');
        }
        if ($demande->demandeur_id !== $demandeur->id) {
            throw new RegleMetierException('Seul le demandeur peut répondre à une demande d\'informations.');
        }
        if (blank($reponse)) {
            throw new RegleMetierException('Saisissez votre réponse.');
        }

        $retour = $demande->statut_retour_complement;
        $niveau = $demande->evenements()->where('action', ActionDemande::DEMANDE_INFORMATIONS)->latest('id')->value('niveau');

        $demande->statut = $retour;
        $demande->statut_retour_complement = null;
        $demande->complement_demande_par_id = null;
        $demande->save();

        Journal::ajouter($demande, $demandeur, ActionDemande::REPONSE_INFORMATIONS,
            DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE, $retour, trim($reponse), $niveau);
    }
}
