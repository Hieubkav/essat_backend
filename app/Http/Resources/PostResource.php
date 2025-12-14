<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasHypermediaLinks;

class PostResource extends BaseResource
{
    use HasHypermediaLinks;

    /**
     * Get resource type cho HATEOAS links
     */
    protected function getResourceType(): string
    {
        return 'posts';
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'active' => $this->active,
            'thumbnail' => $this->thumbnail,
            'order' => $this->order,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            '_links' => $this->generateLinks(),
        ];
    }

    /**
     * Related links cho post
     */
    protected function relatedLinks(): array
    {
        $links = [];

        if ($this->category_id) {
            $links['category'] = [
                'href' => $this->baseUrl() . '/categories/' . $this->category_id,
            ];
        }

        return $links;
    }

    /**
     * Chỉ admin mới có thể update/delete
     */
    protected function canUpdate(): bool
    {
        return request()->user()?->isAdmin() ?? false;
    }

    protected function canDelete(): bool
    {
        return request()->user()?->isAdmin() ?? false;
    }
}
