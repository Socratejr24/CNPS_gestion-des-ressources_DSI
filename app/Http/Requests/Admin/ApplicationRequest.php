<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création / modification d'une application du référentiel (SFD §4.3).
 * L'accès est restreint par le middleware `role:Administrateur` de la route.
 */
class ApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('reference')) {
            $this->merge(['reference' => mb_strtoupper(trim($this->input('reference')))]);
        }
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:50', Rule::unique('applications', 'reference')->ignore($this->route('application'))],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['reference' => 'référence', 'nom' => 'nom', 'description' => 'description'];
    }
}
