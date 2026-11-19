<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // protégé par middleware auth:sanctum
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'nom'          => ['sometimes', 'string', 'max:100'],
            'prenom'       => ['sometimes', 'string', 'max:100'],
            'email'        => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('utilisateurs', 'email')->ignore($userId)],
            'identifiant'  => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('utilisateurs', 'identifiant')->ignore($userId)],
            'matricule'    => ['sometimes', 'nullable', 'string', 'max:50', Rule::unique('utilisateurs', 'matricule')->ignore($userId)],
            'fonction'     => ['sometimes', 'nullable', 'string', 'max:150'],
            'telephone'    => ['sometimes', 'nullable', 'string', 'max:30'],
            'is_active'    => ['sometimes', 'boolean'],
            'type_utilisateur_id' => ['sometimes', 'integer', 'exists:types_utilisateurs,id'],
        
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'        => 'Cet email est déjà utilisé par un autre compte.',
            'identifiant.unique'  => 'Cet identifiant est déjà utilisé.',
            'matricule.unique'    => 'Ce matricule est déjà utilisé.',
        ];
    }
}