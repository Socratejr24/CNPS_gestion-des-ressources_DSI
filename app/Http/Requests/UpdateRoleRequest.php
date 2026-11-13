<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // On récupère l'ID du rôle depuis la route pour ignorer la contrainte d'unicité
        $roleId = $this->route('id'); 

        return [
            'libelle' => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'libelle')->ignore($roleId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        
        ];
    }
}