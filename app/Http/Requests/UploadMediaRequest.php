<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types(config('media.helpers.extensions')())
                    ->max(config('media.max_file_size')),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A file is required for upload.',
            'file.max' => config('media.validation_messages.size'),
            'file.types' => config('media.validation_messages.type'),
            'file.mimes' => config('media.validation_messages.type'),
            'file.mimetypes' => config('media.validation_messages.type'),
        ];
    }
}
