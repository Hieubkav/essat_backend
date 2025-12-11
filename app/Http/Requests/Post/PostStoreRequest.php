<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseFormRequest;

class PostStoreRequest extends BaseFormRequest
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
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug'],
            'content' => ['required', 'string'],
            'active' => ['sometimes', 'boolean'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
