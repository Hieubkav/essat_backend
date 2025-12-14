<?php

namespace App\Http\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Paginated Response Helper
 *
 * Chuẩn hóa response cho pagination theo REST API best practices
 */
class PaginatedResponse
{
    /**
     * Tạo paginated response với metadata đầy đủ
     */
    public static function make(AnonymousResourceCollection $collection): array
    {
        $paginator = $collection->resource;

        if (!$paginator instanceof LengthAwarePaginator) {
            return [
                'items' => $collection->collection,
            ];
        }

        return [
            'items' => $collection->collection,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ];
    }

    /**
     * Tạo paginated response với links (HATEOAS)
     */
    public static function makeWithLinks(AnonymousResourceCollection $collection): array
    {
        $paginator = $collection->resource;

        if (!$paginator instanceof LengthAwarePaginator) {
            return [
                'items' => $collection->collection,
            ];
        }

        return [
            'items' => $collection->collection,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
            '_links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'self' => $paginator->url($paginator->currentPage()),
            ],
        ];
    }
}
