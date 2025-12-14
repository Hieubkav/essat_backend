<?php

namespace App\Http\Resources\Concerns;

/**
 * HATEOAS (Hypermedia as the Engine of Application State) Trait
 *
 * Thêm hypermedia links vào API resources để client có thể navigate
 * mà không cần hard-code URLs
 */
trait HasHypermediaLinks
{
    /**
     * Base URL cho API
     */
    protected function baseUrl(): string
    {
        return config('app.url') . '/api/v1';
    }

    /**
     * Get resource type (must be defined in child class)
     */
    abstract protected function getResourceType(): string;

    /**
     * Generate HATEOAS links cho resource
     */
    protected function generateLinks(): array
    {
        $resourceType = $this->getResourceType();

        if (empty($resourceType)) {
            return [];
        }

        $links = [
            'self' => [
                'href' => $this->selfLink(),
            ],
        ];

        // Thêm các action links nếu có permission (có thể mở rộng sau)
        if ($this->canUpdate()) {
            $links['update'] = [
                'href' => $this->selfLink(),
                'method' => 'PUT',
            ];
        }

        if ($this->canDelete()) {
            $links['delete'] = [
                'href' => $this->selfLink(),
                'method' => 'DELETE',
            ];
        }

        // Thêm related links nếu có
        $relatedLinks = $this->relatedLinks();
        if (!empty($relatedLinks)) {
            $links = array_merge($links, $relatedLinks);
        }

        return $links;
    }

    /**
     * Self link cho resource
     */
    protected function selfLink(): string
    {
        $identifier = $this->resource->slug ?? $this->resource->id;
        $resourceType = $this->getResourceType();
        return "{$this->baseUrl()}/{$resourceType}/{$identifier}";
    }

    /**
     * Check nếu có thể update (override trong child class nếu cần)
     */
    protected function canUpdate(): bool
    {
        // Mặc định cho phép, có thể kiểm tra permission sau
        return true;
    }

    /**
     * Check nếu có thể delete (override trong child class nếu cần)
     */
    protected function canDelete(): bool
    {
        // Mặc định cho phép, có thể kiểm tra permission sau
        return true;
    }

    /**
     * Related links (override trong child class để thêm links liên quan)
     */
    protected function relatedLinks(): array
    {
        return [];
    }
}
