<?php

namespace App\Http\Resources;

class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_admin' => $this->isAdmin(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
