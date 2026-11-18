<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Gestion des utilisateurs par l'administrateur (SFD §3).
 */
interface IUtilisateurService
{
    /**
     * Filtres : search, categorie, service_id, is_active, sans_affectation.
     */
    public function lister(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function charger(User $utilisateur): User;

    /** Création d'un compte local prestataire (RG-08). */
    public function creerPrestataire(array $data): User;

    public function modifier(User $utilisateur, array $data): User;

    public function changerStatut(User $utilisateur, bool $actif): User;

    /** Remplace l'ensemble des rôles fonctionnels de l'utilisateur. */
    public function attribuerRoles(User $utilisateur, array $roleIds): User;
}
