<?php

/*
|--------------------------------------------------------------------------
| Annuaire LDAP de la CNPS (authentification des agents, RG-07)
|--------------------------------------------------------------------------
|
| driver :
|   - "native" : connexion réelle via l'extension PHP ldap.
|   - "fake"   : annuaire simulé (comptes ci-dessous). Ignoré en production.
|
| Principe de l'authentification "native" :
|   1. bind avec le compte de service (bind_dn / bind_password) ;
|   2. recherche de l'agent avec user_filter (%s = identifiant saisi, échappé) ;
|   3. bind avec le DN trouvé et le mot de passe saisi.
|
*/

return [

    'driver' => env('LDAP_DRIVER', 'native'),

    'host' => env('LDAP_HOST', '127.0.0.1'),
    'port' => (int) env('LDAP_PORT', 389),
    'use_ssl' => (bool) env('LDAP_SSL', false),     // ldaps://
    'use_tls' => (bool) env('LDAP_TLS', false),     // StartTLS
    'timeout' => (int) env('LDAP_TIMEOUT', 5),

    'base_dn' => env('LDAP_BASE_DN', 'dc=cnps,dc=ci'),
    'bind_dn' => env('LDAP_BIND_DN'),
    'bind_password' => env('LDAP_BIND_PASSWORD'),

    // Active Directory : (&(objectClass=user)(sAMAccountName=%s))
    // OpenLDAP          : (&(objectClass=inetOrgPerson)(uid=%s))
    'user_filter' => env('LDAP_USER_FILTER', '(&(objectClass=user)(sAMAccountName=%s))'),

    // Correspondance attributs LDAP -> profil local
    'attributes' => [
        'matricule' => env('LDAP_ATTR_MATRICULE', 'sAMAccountName'),
        'nom' => env('LDAP_ATTR_NOM', 'sn'),
        'prenom' => env('LDAP_ATTR_PRENOM', 'givenName'),
        'email' => env('LDAP_ATTR_EMAIL', 'mail'),
        'fonction' => env('LDAP_ATTR_FONCTION', 'title'),
    ],

    /*
    | Comptes de l'annuaire simulé (driver "fake", local/test uniquement).
    | Format : identifiant de connexion => [password, matricule, nom, prenom, email, fonction]
    | (cf. OrganisationDemoSeeder pour les affectations correspondantes)
    */
    'fake_accounts' => [
        'm1001' => ['password', 'm1001', 'Adou', 'Kouassi', 'kouassi.adou@cnps.ci', 'Développeur'],
        'm1002' => ['password', 'm1002', 'Aya', 'Koffi', 'koffi.aya@cnps.ci', 'Chef de service'],
        'm1003' => ['password', 'm1003', 'Yao', 'Konan', 'konan.yao@cnps.ci', 'Responsable de département'],
        // Jamais connecté : sera créé à sa première connexion, sans affectation.
        'm1004' => ['password', 'm1004', 'Agent', 'Nouvel', 'nouvel.agent@cnps.ci', 'Analyste'],
    ],
];
