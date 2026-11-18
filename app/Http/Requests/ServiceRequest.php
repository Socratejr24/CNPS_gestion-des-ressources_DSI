<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création (POST) et modification (PUT/PATCH) d'un service.
 */
class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // TODO: réservé à l'administrateur (RG-05)
    }

    public function rules(): array
    {
        $requis = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'departement_id' => [$requis, 'integer', 'exists:departements,id'],
            'code' => [$requis, 'string', 'max:30', Rule::unique('services', 'code')->ignore($this->route('service'))],
            'libelle' => [$requis, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
