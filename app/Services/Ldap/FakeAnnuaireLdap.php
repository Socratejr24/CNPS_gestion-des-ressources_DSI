<?php

namespace App\Services\Ldap;

/**
 * Annuaire simulé pour le développement local et les tests.
 * Jamais utilisé en production (cf. AppServiceProvider).
 */
class FakeAnnuaireLdap implements AnnuaireLdap
{
    /**
     * @param  array<string, array{0:string,1:string,2:string,3:string,4:?string,5:?string}>  $comptes
     */
    public function __construct(private array $comptes = []) {}

    public function authentifier(string $identifiant, string $motDePasse): ?ProfilLdap
    {
        $compte = $this->comptes[$identifiant] ?? null;

        if ($compte === null || $motDePasse === '' || ! hash_equals($compte[0], $motDePasse)) {
            return null;
        }

        return new ProfilLdap(
            matricule: $compte[1],
            nom: $compte[2],
            prenom: $compte[3],
            email: $compte[4] ?? null,
            fonction: $compte[5] ?? null,
        );
    }

    public function ajouterCompte(string $identifiant, string $motDePasse, ProfilLdap $profil): void
    {
        $this->comptes[$identifiant] = [
            $motDePasse, $profil->matricule, $profil->nom, $profil->prenom, $profil->email, $profil->fonction,
        ];
    }
}
