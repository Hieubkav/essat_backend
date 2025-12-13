<?php

namespace App\Repositories;

use App\Models\ProductCategory;

class ProductCategoryRepository extends BaseRepository implements ProductCategoryRepositoryInterface
{
    public function __construct()
    {
        $this->model = new ProductCategory();
    }

    public function getActive(int $perPage = 15)
    {
        return $this->model
            ->where('active', true)
            ->orderBy('order')
            ->paginate($perPage);
    }

    public function findActiveBySlug(string $slug): ?ProductCategory
    {
        return $this->model
            ->where('slug', $slug)
            ->where('active', true)
            ->first();
    }
}
