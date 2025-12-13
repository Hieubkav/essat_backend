<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct()
    {
        $this->model = new Category();
    }

    public function getActive(int $perPage = 15)
    {
        return $this->model
            ->where('active', true)
            ->orderBy('order')
            ->paginate($perPage);
    }

    public function findActiveBySlug(string $slug): ?Category
    {
        return $this->model
            ->where('slug', $slug)
            ->where('active', true)
            ->first();
    }

    public function getAllActive()
    {
        return $this->model
            ->where('active', true)
            ->orderBy('order')
            ->get();
    }
}
