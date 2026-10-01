<?php

/**
 * Helper de permissions — PLACEHOLDER.
 *
 * ⚠️ Ce helper sera remplacé par le vrai système de permissions
 * (Spatie Permission ou custom) quand on branchera l'authentification.
 *
 * Pour l'instant, il retourne TOUJOURS true afin de pouvoir
 * visualiser toutes les sections de la sidebar pendant la phase maquette.
 */

if (! function_exists('user_can')) {
    function user_can(string $permission): bool
    {
        // TODO: brancher le vrai système de permissions
        // Pour l'instant, on simule les permissions pour tester la sidebar
        return true;
    }
}

if (! function_exists('current_user')) {
    /**
     * Retourne l'utilisateur connecté — PLACEHOLDER.
     * ⚠️ À remplacer par auth()->user() quand on aura l'auth.
     */
    function current_user(): ?object
    {
        // Utilisateur factice pour tester le layout
        return (object) [
            'nom'      => 'Kouassi',
            'prenom'   => 'A.',
            'initiales'=> 'KA',
            'matricule'=> '123456890',
            'roles'    => ['Demandeur', 'Receveur'],
            'email'    => 'kouassi.a@cnps.ci',
        ];
    }
}