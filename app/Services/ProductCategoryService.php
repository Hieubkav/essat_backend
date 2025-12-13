<?php

namespace App\Services;

use App\Models\ProductCategory;
use App\Repositories\ProductCategoryRepository;
use Illuminate\Support\Str;

class ProductCategoryService extends BaseService
{
    public function __construct(
        private ProductCategoryRepository $categories
    ) {
    }

    public function listActive(int $perPage = 15)
    {
        return $this->categories->getActive($perPage);
    }

    public function list(int $perPage = 15)
    {
        return $this->categories->paginate($perPage);
    }

    public function findBySlug(string $slug): ?ProductCategory
    {
        return $this->categories->findActiveBySlug($slug);
    }

    public function find(int|string $id): ProductCategory
    {
        return $this->categories->findOrFail($id);
    }

    public function create(array $data): ProductCategory
    {
        $data['slug'] ??= Str::slug($data['name']);

        return $this->categories->create($data);
    }

    public function update(ProductCategory $category, array $data): ProductCategory
    {
        $this->categories->update($category->id, $data);

        return $category->refresh();
    }

    public function delete(ProductCategory $category): bool
    {
        return $this->categories->delete($category->id);
    }
}
