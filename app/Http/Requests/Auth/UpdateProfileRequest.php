<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class UpdateProfileRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $userId],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Email must be valid',
            'email.unique' => 'Email already exists',
        ];
    }
}
