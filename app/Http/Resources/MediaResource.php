<?php

namespace App\Http\Resources;

class MediaResource extends BaseResource
{
    public function toArray($request): array
    {
        $thumbUrl = $this->hasGeneratedConversion('thumb')
            ? $this->getFullUrl('thumb')
            : $this->getFullUrl();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'collection_name' => $this->collection_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => $this->getFullUrl(),
            'thumbnail_url' => $thumbUrl,
            'custom_properties' => $this->custom_properties,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
