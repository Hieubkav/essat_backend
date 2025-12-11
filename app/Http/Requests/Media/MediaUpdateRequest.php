<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\BaseFormRequest;

class MediaUpdateRequest extends BaseFormRequest
{
    /**
     * Validation rules for updating media metadata.
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }
}
