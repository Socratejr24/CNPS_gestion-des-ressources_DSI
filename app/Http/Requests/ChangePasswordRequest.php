<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Protégé par middleware auth:sanctum
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password'         => [
                'required',
                'string',
                'confirmed',
                'different:current_password', //  Différent de l'ancien
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'password.required'         => 'Le nouveau mot de passe est requis.',
            'password.confirmed'        => 'Les mots de passe ne correspondent pas.',
            'password.different'        => 'Le nouveau mot de passe doit être différent de l\'ancien.',
            'password.min'              => 'Le mot de passe doit contenir au moins 8 caractères.',
        ];
    }
}