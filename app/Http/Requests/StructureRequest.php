<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création (POST) et modification (PUT/PATCH) d'une structure.
 */
class StructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // TODO: réservé à l'administrateur (RG-05) quand l'auth sera branchée
    }

    public function rules(): array
    {
        $requis = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'code' => [$requis, 'string', 'max:30', Rule::unique('structures', 'code')->ignore($this->route('structure'))],
            'libelle' => [$requis, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
