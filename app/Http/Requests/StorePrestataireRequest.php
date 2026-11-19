<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrestataireRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nom'      => ['required', 'string', 'max:100'],
            'prenom'   => ['required', 'string', 'max:100'],
            'email'    => ['nullable', 'email', 'max:255', 'unique:utilisateurs,email'], 
            'fonction' => ['nullable', 'string', 'max:150'],
        
        ];
    }
}