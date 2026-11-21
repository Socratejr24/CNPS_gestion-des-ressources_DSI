<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création / modification d'une permission depuis l'interface d'administration.
 * L'accès est restreint par le middleware `role:Administrateur` de la route.
 */
class PermissionFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) {
            $this->merge(['slug' => mb_strtolower(trim($this->input('slug')))]);
        }
    }

    public function rules(): array
    {
        return [
            'libelle' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]+([._-][a-z0-9]+)*$/',
                Rule::unique('permissions', 'slug')->ignore($this->route('permission')),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Le slug ne contient que des minuscules, chiffres, points, tirets ou soulignés (ex. voir.traitement).',
        ];
    }

    public function attributes(): array
    {
        return ['libelle' => 'libellé', 'slug' => 'slug', 'description' => 'description'];
    }
}
