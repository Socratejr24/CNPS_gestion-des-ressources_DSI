<?php

/**
 * Helpers d'affichage utilisés par les vues Blade (sidebar, profil, dashboard).
 */

use App\Models\User;

if (! function_exists('user_can')) {
    /**
     * Vrai si l'utilisateur connecté possède la permission (slug) via l'un de ses rôles actifs.
     */
    function user_can(string $permission): bool
    {
        return auth()->user()?->hasPermission($permission) ?? false;
    }
}

if (! function_exists('current_user')) {
    /**
     * Vue « affichage » de l'utilisateur connecté.
     * Conserve la forme attendue par les vues (chaînes et tableau de rôles).
     */
    function current_user(): object
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return (object) [
                'nom' => '', 'prenom' => 'Invité', 'initiales' => '?', 'matricule' => '—',
                'email' => '—', 'fonction' => '—', 'structure' => '—', 'departement' => '—',
                'service' => '—', 'roles' => [],
            ];
        }

        $user->loadMissing(['service.departement.structure', 'roles']);
        $departement = $user->service?->departement;

        return (object) [
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'initiales' => mb_strtoupper(mb_substr($user->prenom, 0, 1).mb_substr($user->nom, 0, 1)),
            'matricule' => $user->matricule ?? '—',
            'email' => $user->email,
            'fonction' => $user->fonction ?? '—',
            'categorie' => $user->categorie?->label(),
            'structure' => $departement?->structure?->libelle ?? '—',
            'departement' => $departement?->libelle ?? '—',
            // L'administrateur est dispensé d'affectation (RG-11) : pas de « en attente » pour lui.
            'service' => $user->service?->libelle ?? ($user->estAdministrateur() ? 'Administrateur' : 'Affectation en attente'),
            'roles' => $user->roles->where('is_active', true)->pluck('libelle')->values()->all(),
        ];
    }
}
