<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:51200', // 50MB in KB, valido come limite generale
                'mimes:jpg,jpeg,png,webp,gif,mp4,mov,webm,mp3,wav,m4a',
            ],
            'page_id' => ['nullable', 'exists:pages,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Formato file non supportato. Sono ammessi: immagini (jpg, png, webp, gif), video (mp4, mov, webm), audio (mp3, wav, m4a).',
            'file.max' => 'Il file supera la dimensione massima consentita (50MB).',
        ];
    }
}