<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * POST : création d'un compte local prestataire (RG-08).
 * PUT/PATCH : modification d'un utilisateur (le mot de passe est ignoré pour les agents LDAP).
 *
 * Les agents CNPS sont créés automatiquement à leur première connexion LDAP (RG-09).
 */
class UtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // TODO: réservé à l'administrateur
    }

    public function rules(): array
    {
        $creation = $this->isMethod('post');
        $requis = $creation ? 'required' : 'sometimes';
        $id = $this->route('utilisateur');

        return [
            'nom' => [$requis, 'string', 'max:255'],
            'prenom' => [$requis, 'string', 'max:255'],
            'email' => [$requis, 'email', 'max:255', Rule::unique('utilisateurs', 'email')->ignore($id)],
            'identifiant' => [$requis, 'string', 'max:255', Rule::unique('utilisateurs', 'identifiant')->ignore($id)],
            'matricule' => ['nullable', 'string', 'max:50', Rule::unique('utilisateurs', 'matricule')->ignore($id)],
            'fonction' => ['nullable', 'string', 'max:255'],
            'password' => [$creation ? 'required' : 'nullable', Password::min(8)->letters()->numbers()],
        ];
    }
}
