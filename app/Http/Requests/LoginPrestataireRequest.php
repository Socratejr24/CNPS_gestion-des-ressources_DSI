<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginPrestataireRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'identifiant' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}