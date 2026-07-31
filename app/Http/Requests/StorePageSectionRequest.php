<?php

namespace App\Http\Requests;

use App\Models\PageSection;
use Illuminate\Foundation\Http\FormRequest;

class StorePageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:text,photo,song,timeline,playlist,countdown,video,letter'],
            'content' => ['required', 'array'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}