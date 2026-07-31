<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // il controllo utente autenticato lo fa il middleware auth:sanctum
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'occasion' => ['nullable', 'string'],
            'theme_id' => ['nullable', 'exists:themes,id'],
            'custom_colors' => ['nullable', 'array'],
            'custom_fonts' => ['nullable', 'array'],
        ];
    }
}