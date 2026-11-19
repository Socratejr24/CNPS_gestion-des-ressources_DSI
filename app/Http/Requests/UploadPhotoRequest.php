<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'image',          // vérifie que c'est une image
                'mimes:jpg,jpeg,png,webp',
                'max:2048',       // 2 Mo max
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Aucune photo fournie.',
            'photo.image'    => 'Le fichier doit être une image.',
            'photo.mimes'    => 'Format accepté : JPG, PNG ou WebP.',
            'photo.max'      => 'La photo ne doit pas dépasser 2 Mo.',
        ];
    }
}