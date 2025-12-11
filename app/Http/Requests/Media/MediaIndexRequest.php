<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\BaseFormRequest;

class MediaIndexRequest extends BaseFormRequest
{
    /**
     * Validation rules for listing media.
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:255'],
            'collection_name' => ['nullable', 'string', 'max:255'],
            'model_type' => ['nullable', 'string', 'max:255'],
            'model_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
