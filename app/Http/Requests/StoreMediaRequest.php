<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
                'mimes:jpg,jpeg,png,webp,gif,mp4,mov,webm,mp3,wav,m4a',
            ],
            'page_id' => ['nullable', 'exists:pages,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Formato file non supportato. Sono ammessi: immagini (jpg, png, webp, gif), video (mp4, mov, webm), audio (mp3, wav, m4a).',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $file = $this->file('file');

            if (! $file) {
                return;
            }

            $mimeType = $file->getMimeType();
            $sizeInKb = $file->getSize() / 1024;

            $limits = [
                'image' => 15 * 1024,   // 15MB per le foto
                'video' => 200 * 1024,  // 200MB per i video
                'audio' => 30 * 1024,   // 30MB per l'audio
            ];

            $category = match (true) {
                Str::startsWith($mimeType, 'image/') => 'image',
                Str::startsWith($mimeType, 'video/') => 'video',
                Str::startsWith($mimeType, 'audio/') => 'audio',
                default => null,
            };

            if ($category && $sizeInKb > $limits[$category]) {
                $maxMb = $limits[$category] / 1024;
                $validator->errors()->add('file', "Il file supera la dimensione massima consentita per questo tipo ({$maxMb}MB).");
            }
        });
    }
}
