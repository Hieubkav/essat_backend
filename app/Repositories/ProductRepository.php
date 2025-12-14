<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct()
    {
        $this->model = new Product();
    }

    public function getActive(int $perPage = 15, ?int $categoryId = null)
    {
        $query = $this->model
            ->with('categories')
            ->where('active', true)
            ->orderBy('order');

        if ($categoryId) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('product_categories.id', $categoryId);
            });
        }

        return $query->paginate($perPage);
    }

    public function findActiveBySlug(string $slug): ?Product
    {
        return $this->model
            ->where('slug', $slug)
            ->where('active', true)
            ->first();
    }

    public function getFeatured(int $limit = 8)
    {
        return $this->model
            ->with('categories')
            ->where('active', true)
            ->orderBy('order')
            ->limit($limit)
            ->get();
    }
}
