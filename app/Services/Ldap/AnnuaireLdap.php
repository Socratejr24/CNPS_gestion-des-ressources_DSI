<?php

namespace App\Services\Ldap;

interface AnnuaireLdap
{
    /**
     * Vérifie les identifiants auprès de l'annuaire (SFD §3.1.1, étape 1).
     *
     * @return ProfilLdap|null le profil si les identifiants sont valides, null sinon
     *
     * @throws AnnuaireIndisponibleException si l'annuaire ne répond pas
     */
    public function authentifier(string $identifiant, string $motDePasse): ?ProfilLdap;
}
