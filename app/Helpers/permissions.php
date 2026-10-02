<?php

/**
 * Helper de permissions — PLACEHOLDER.
 * ⚠️ Sera remplacé par le vrai système (Spatie) quand on branchera l'auth.
 */

if (! function_exists('user_can')) {
    function user_can(string $permission): bool
    {
        // TODO: brancher le vrai système de permissions
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
        return (object) [
            'nom'       => 'Adou',
            'prenom'    => 'Kouassi',
            'initiales' => 'KA',
            'matricule' => '104582',
            'email'     => 'kouassi.adou@cnps.ci',
            'fonction'  => 'Développeur',
            'structure' => 'DSI',
            'departement' => 'DEV',
            'service'     => 'Développement IT',
            'roles'     => ['Demandeur', 'Receveur'],
        ];
    }
}