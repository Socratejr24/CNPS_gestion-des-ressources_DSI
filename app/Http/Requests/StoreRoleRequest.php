<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // On gérera les permissions plus tard
    }

public function rules(): array
{
    return [
        'libelle' => ['required', 'string', 'max:255', 'unique:roles,libelle'],
        'description' => ['nullable', 'string'],
        'is_active' => ['boolean'],
    ];
}
}