<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Str;

class PostUpdateRequest extends BaseFormRequest
{
    /**
     * Prepare data before validation.
     *
     * If only title is provided and slug is missing, auto-generate slug so the unique rule validates it.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('title') && !$this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->input('title')),
            ]);
        }
    }

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
        $postId = $this->route('post')?->id ?? $this->route('post');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:posts,slug,' . $postId],
            'content' => ['sometimes', 'string'],
            'active' => ['sometimes', 'boolean'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
