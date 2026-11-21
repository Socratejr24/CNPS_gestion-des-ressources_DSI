<?php

namespace App\Services\Ldap;

/**
 * Informations d'un agent renvoyées par l'annuaire après authentification réussie.
 */
final readonly class ProfilLdap
{
    public function __construct(
        public string $matricule,
        public string $nom,
        public string $prenom,
        public ?string $email,
        public ?string $fonction = null,
    ) {}
}
