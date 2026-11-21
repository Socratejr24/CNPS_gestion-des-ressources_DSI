<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;

/**
 * Qui peut consulter ou agir sur une demande (RG-34).
 *
 * Étape 2 : demandeur et administrateur (consultation, SFD §3.3.1).
 * Les étapes suivantes ajouteront les valideurs N+1 / N+2 et les receveurs.
 */
class DemandePolicy
{
    public function view(User $utilisateur, Demande $demande): bool
    {
        return $demande->demandeur_id === $utilisateur->id
            || $utilisateur->estAdministrateur();
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
}
