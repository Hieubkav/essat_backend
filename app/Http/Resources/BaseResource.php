<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base Resource
 * 
 * Provides common transformation methods for all API resources
 */
abstract class BaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        // Default: return original attributes; override in child if needed
        return parent::toArray($request);
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with($request): array
    {
        return [];
    }
}
