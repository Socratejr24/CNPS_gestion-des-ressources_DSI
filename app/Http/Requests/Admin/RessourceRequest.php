<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création / modification d'une ressource du référentiel (SFD §5.3).
 * L'accès est restreint par le middleware `role:Administrateur` de la route.
 */
class RessourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_filter([
            'reference' => $this->filled('reference') ? mb_strtoupper(trim($this->input('reference'))) : null,
            // « matériel » et « Matériel » doivent désigner la même catégorie.
            'categorie' => $this->filled('categorie') ? $this->majusculeInitiale(trim($this->input('categorie'))) : null,
        ]));
    }

    private function majusculeInitiale(string $texte): string
    {
        return mb_strtoupper(mb_substr($texte, 0, 1)).mb_substr($texte, 1);
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:50', Rule::unique('ressources', 'reference')->ignore($this->route('ressource'))],
            'designation' => ['required', 'string', 'max:255'],
            'categorie' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'informations_complementaires' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reference' => 'référence',
            'designation' => 'désignation',
            'categorie' => 'catégorie',
            'informations_complementaires' => 'informations complémentaires',
        ];
    }
}
