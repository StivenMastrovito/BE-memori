<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('page')->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'occasion' => ['nullable', 'string',],
            'theme_id' => ['nullable', 'exists:themes,id'],
            'custom_colors' => ['nullable', 'array'],
            'custom_fonts' => ['nullable', 'array'],
            'cover_image_url' => ['nullable', 'string', 'max:2048'],
            'background_pattern' => ['nullable', 'string', 'in:hearts,dots,waves,stars,confetti,leaves,flowers,money,animals, none'],
        ];
    }
}
