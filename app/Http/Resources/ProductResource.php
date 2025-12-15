<?php

namespace App\Http\Resources;

use App\Helpers\PlaceholderHelper;
use App\Http\Resources\Concerns\HasHypermediaLinks;
use Illuminate\Support\Facades\Storage;

class ProductResource extends BaseResource
{
    use HasHypermediaLinks;

    protected $relatedProducts = null;

    protected function getResourceType(): string
    {
        return 'products';
    }

    public function withRelated($products): self
    {
        $this->relatedProducts = $products;

        return $this;
    }

    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'content' => $this->content,
            'thumbnail' => $this->thumbnail
                ? Storage::disk('public')->url($this->thumbnail)
                : PlaceholderHelper::getUrl(),
            'price' => $this->price,
            'active' => $this->active,
            'order' => $this->order,
            'categories' => ProductCategoryResource::collection($this->whenLoaded('categories')),
            'images' => $this->getMediaUrls(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            '_links' => $this->generateLinks(),
        ];

        if ($this->relatedProducts !== null) {
            $data['related_products'] = $this->relatedProducts->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'thumbnail' => $p->thumbnail
                    ? Storage::disk('public')->url($p->thumbnail)
                    : PlaceholderHelper::getUrl(),
                'price' => $p->price,
                'category' => $p->categories->first()?->name,
            ]);
        }

        return $data;
    }

    protected function getMediaUrls(): array
    {
        return $this->resource->getMedia('images')->map(fn ($media) => $media->getUrl())->toArray();
    }

    /**
     * Related links cho product
     */
    protected function relatedLinks(): array
    {
        return [
            'categories' => [
                'href' => $this->baseUrl().'/product-categories?product_id='.$this->id,
            ],
        ];
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
