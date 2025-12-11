<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class UserUpdateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $userId],
            'role' => ['sometimes', 'in:user,admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Email must be valid',
            'email.unique' => 'Email already exists',
            'role.in' => 'Role must be user or admin',
        ];
    }
}
