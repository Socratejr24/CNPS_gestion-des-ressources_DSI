<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTypeUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Protégé plus tard par middleware
    }

    public function rules(): array
    {
        return [
            'libelle' => ['required', 'string', 'max:100', 'unique:types_utilisateurs,libelle'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}