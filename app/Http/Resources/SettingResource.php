<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Storage;

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
            'logo' => $this->logo ? Storage::disk('public')->url($this->logo) : null,
            'favicon' => $this->favicon ? Storage::disk('public')->url($this->favicon) : null,
            'placeholder' => $this->placeholder ? Storage::disk('public')->url($this->placeholder) : null,
            'updated_at' => $this->updated_at,
        ];
    }
}
