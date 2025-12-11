<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\BaseFormRequest;

class SettingUpdateRequest extends BaseFormRequest
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
            'site_name' => ['sometimes', 'string', 'max:255'],
            'primary_color' => ['sometimes', 'string', 'max:32'],
            'secondary_color' => ['sometimes', 'string', 'max:32'],
            'seo_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_description' => ['sometimes', 'nullable', 'string'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
        ];
    }
}
