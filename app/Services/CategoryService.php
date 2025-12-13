<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Str;

class CategoryService extends BaseService
{
    public function __construct(
        private CategoryRepository $categories
    ) {
    }

    public function listActive(int $perPage = 15)
    {
        return $this->categories->getActive($perPage);
    }

    public function getAllActive()
    {
        return $this->categories->getAllActive();
    }

    public function list(int $perPage = 15)
    {
        return $this->categories->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->categories->findActiveBySlug($slug);
    }

    public function find(int|string $id): Category
    {
        return $this->categories->findOrFail($id);
    }

    public function create(array $data): Category
    {
        $data['slug'] ??= Str::slug($data['name']);

        return $this->categories->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $this->categories->update($category->id, $data);

        return $category->refresh();
    }

    public function delete(Category $category): bool
    {
        return $this->categories->delete($category->id);
    }
}
