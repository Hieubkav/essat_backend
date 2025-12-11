<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\BaseFormRequest;

class SettingStoreRequest extends BaseFormRequest
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
            'site_name' => ['required', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'max:32'],
            'secondary_color' => ['required', 'string', 'max:32'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
