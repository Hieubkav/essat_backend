<?php

namespace App\Repositories;

use App\Models\Post;

/**
 * Post Repository
 */
class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct()
    {
        $this->model = new Post();
    }

    public function getActive(int $perPage = 15, ?int $categoryId = null)
    {
        $query = $this->model
            ->where('active', true)
            ->orderBy('order');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->paginate($perPage);
    }

    public function findActiveBySlug(string $slug): ?Post
    {
        return $this->model
            ->where('slug', $slug)
            ->where('active', true)
            ->first();
    }

    public function getLatest(int $limit = 6)
    {
        return $this->model
            ->where('active', true)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get related posts by category (exclude current post).
     */
    public function getRelatedByCategory(int $categoryId, int $excludePostId, int $limit = 6)
    {
        return $this->model
            ->where('active', true)
            ->where('category_id', $categoryId)
            ->where('id', '!=', $excludePostId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
