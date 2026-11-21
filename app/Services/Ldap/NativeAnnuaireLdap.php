<?php

namespace App\Services\Ldap;

use Illuminate\Support\Facades\Log;
use LDAP\Connection;

/**
 * Authentification auprès de l'annuaire de la CNPS via l'extension PHP ldap.
 *
 * search-then-bind : bind de service → recherche du DN de l'agent → bind de l'agent.
 */
class NativeAnnuaireLdap implements AnnuaireLdap
{
    public function __construct(private array $config) {}

    public function authentifier(string $identifiant, string $motDePasse): ?ProfilLdap
    {
        // Un bind avec mot de passe vide est un bind anonyme qui RÉUSSIT : à refuser.
        if (trim($identifiant) === '' || $motDePasse === '') {
            return null;
        }

        if (! function_exists('ldap_connect')) {
            throw new AnnuaireIndisponibleException("L'extension PHP ldap n'est pas activée.");
        }

        $connexion = $this->connecter();

        try {
            if (! @ldap_bind($connexion, $this->config['bind_dn'], $this->config['bind_password'])) {
                Log::error('LDAP : échec du bind de service', ['erreur' => ldap_error($connexion)]);
                throw new AnnuaireIndisponibleException('Connexion à l\'annuaire impossible.');
            }

            $entree = $this->rechercher($connexion, $identifiant);
            if ($entree === null) {
                return null;
            }

            if (! @ldap_bind($connexion, $entree['dn'], $motDePasse)) {
                return null; // mot de passe incorrect, compte verrouillé, etc.
            }

            return $this->versProfil($entree, $identifiant);
        } finally {
            @ldap_unbind($connexion);
        }
    }

    /**
     * @return Connection
     */
    private function connecter()
    {
        $schema = $this->config['use_ssl'] ? 'ldaps' : 'ldap';
        $connexion = @ldap_connect("{$schema}://{$this->config['host']}:{$this->config['port']}");

        if ($connexion === false) {
            throw new AnnuaireIndisponibleException('Adresse de l\'annuaire invalide.');
        }

        ldap_set_option($connexion, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connexion, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($connexion, LDAP_OPT_NETWORK_TIMEOUT, $this->config['timeout']);

        if ($this->config['use_tls'] && ! @ldap_start_tls($connexion)) {
            throw new AnnuaireIndisponibleException('Échec de la négociation TLS avec l\'annuaire.');
        }

        return $connexion;
    }

    /**
     * @param  Connection  $connexion
     */
    private function rechercher($connexion, string $identifiant): ?array
    {
        // Échappement contre l'injection LDAP.
        $filtre = sprintf($this->config['user_filter'], ldap_escape($identifiant, '', LDAP_ESCAPE_FILTER));
        $attributs = array_values($this->config['attributes']);

        $resultat = @ldap_search($connexion, $this->config['base_dn'], $filtre, $attributs, 0, 2);
        if ($resultat === false) {
            throw new AnnuaireIndisponibleException('Recherche dans l\'annuaire impossible.');
        }

        $entrees = ldap_get_entries($connexion, $resultat);

        // 0 entrée : inconnu ; plusieurs : ambigu → refus.
        return ($entrees['count'] ?? 0) === 1 ? $entrees[0] : null;
    }

    private function versProfil(array $entree, string $identifiant): ProfilLdap
    {
        $valeur = function (string $champ) use ($entree): ?string {
            $attribut = strtolower($this->config['attributes'][$champ]);

            return $entree[$attribut][0] ?? null;
        };

        return new ProfilLdap(
            matricule: $valeur('matricule') ?? $identifiant,
            nom: $valeur('nom') ?? $identifiant,
            prenom: $valeur('prenom') ?? '',
            email: $valeur('email'),
            fonction: $valeur('fonction'),
        );
    }
}
