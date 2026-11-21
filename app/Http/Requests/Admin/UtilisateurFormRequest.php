<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\UtilisateurRequest;

/**
 * Formulaire Blade de création / modification d'un compte prestataire.
 * Mêmes règles que l'API, avec confirmation du mot de passe.
 * L'accès est restreint par le middleware `role:Administrateur` de la route.
 */
class UtilisateurFormRequest extends UtilisateurRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['password'][] = 'confirmed';

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'nom' => 'nom',
            'prenom' => 'prénom',
            'email' => 'adresse e-mail',
            'identifiant' => 'identifiant de connexion',
            'matricule' => 'matricule',
            'fonction' => 'fonction',
            'password' => 'mot de passe',
        ];
    }
}
