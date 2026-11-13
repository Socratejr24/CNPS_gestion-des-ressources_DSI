<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // <-- On autorise pour l'instant
    }

    public function rules(): array
    {
        $permissionId = $this->route('id');

        return [
            'libelle' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('permissions', 'slug')->ignore($permissionId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            // 'modifie_par' => ['nullable', 'integer', 'exists:utilisateurs,id'],
        ];
    }
}