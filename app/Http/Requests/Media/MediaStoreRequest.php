<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\BaseFormRequest;

class MediaStoreRequest extends BaseFormRequest
{
    /**
     * Validation rules for uploading media.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10 MB
                'mimes:jpg,jpeg,png,webp,avif,gif,svg,pdf',
            ],
            'collection_name' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }
}
