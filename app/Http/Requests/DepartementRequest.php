<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création (POST) et modification (PUT/PATCH) d'un département.
 */
class DepartementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // TODO: réservé à l'administrateur (RG-05)
    }

    public function rules(): array
    {
        $requis = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'structure_id' => [$requis, 'integer', 'exists:structures,id'],
            'code' => [$requis, 'string', 'max:30', Rule::unique('departements', 'code')->ignore($this->route('departement'))],
            'libelle' => [$requis, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
