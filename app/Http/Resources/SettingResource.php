<?php

namespace App\Http\Resources;

class SettingResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'site_name' => $this->site_name,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'phone' => $this->phone,
            'address' => $this->address,
            'email' => $this->email,
            'updated_at' => $this->updated_at,
        ];
    }
}
