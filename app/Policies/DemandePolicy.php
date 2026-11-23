<?php

namespace App\Policies;

use App\Enums\DemandeStatut;
use App\Models\Demande;
use App\Models\User;
use App\Services\Demandes\Valideurs;

/**
 * Qui peut consulter ou agir sur une demande (RG-34, SFD §16.1 : impossible de
 * contourner les validations par un accès direct).
 *
 * Consultation : demandeur, administrateur, valideur attendu pour l'étape en cours,
 * et toute personne ayant déjà agi sur la demande (valideur ou auteur d'une question).
 * L'étape 4 ajoutera les receveurs du périmètre de destination.
 */
class DemandePolicy
{
    public function view(User $utilisateur, Demande $demande): bool
    {
        return $demande->demandeur_id === $utilisateur->id
            || $utilisateur->estAdministrateur()
            || Valideurs::estValideurAttendu($utilisateur, $demande)
            || $demande->evenements()->where('utilisateur_id', $utilisateur->id)->exists();
    }

    /** Modifier, supprimer ou soumettre : l'auteur, tant que la demande est en brouillon (SFD §6.4). */
    public function update(User $utilisateur, Demande $demande): bool
    {
        return $demande->demandeur_id === $utilisateur->id && $demande->estBrouillon();
    }

    public function delete(User $utilisateur, Demande $demande): bool
    {
        return $this->update($utilisateur, $demande);
    }

    public function soumettre(User $utilisateur, Demande $demande): bool
    {
        return $this->update($utilisateur, $demande);
    }

    /** Valider, rejeter ou demander des informations à l'étape N+1 / N+2 en cours. */
    public function decider(User $utilisateur, Demande $demande): bool
    {
        return Valideurs::estValideurAttendu($utilisateur, $demande);
    }

    /** Répondre à une demande d'informations complémentaires (SFD §10.3). */
    public function repondre(User $utilisateur, Demande $demande): bool
    {
        return $demande->demandeur_id === $utilisateur->id
            && $demande->statut === DemandeStatut::INFO_COMPLEMENTAIRE_DEMANDEE;
    }
}
