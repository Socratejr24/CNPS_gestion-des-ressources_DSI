<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création / modification d'un rôle depuis l'interface d'administration.
 * L'accès est restreint par le middleware `role:Administrateur` de la route.
 */
class RoleFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // L'unicité porte aussi sur les rôles supprimés (contrainte UNIQUE en base).
            'libelle' => ['required', 'string', 'max:255', Rule::unique('roles', 'libelle')->ignore($this->route('role'))],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return ['libelle' => 'libellé', 'description' => 'description'];
    }
}
