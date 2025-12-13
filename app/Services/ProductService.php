<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Str;

class ProductService extends BaseService
{
    public function __construct(
        private ProductRepository $products,
        private ContentImageService $contentImages
    ) {
    }

    public function listActive(int $perPage = 15, ?int $categoryId = null)
    {
        return $this->products->getActive($perPage, $categoryId);
    }

    public function list(int $perPage = 15)
    {
        return $this->products->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->products->findActiveBySlug($slug);
    }

    public function find(int|string $id): Product
    {
        return $this->products->findOrFail($id);
    }

    public function getFeatured(int $limit = 8)
    {
        return $this->products->getFeatured($limit);
    }

    public function create(array $data): Product
    {
        $data['slug'] ??= Str::slug($data['name']);

        if (isset($data['content'])) {
            $data['content'] = $this->contentImages->replaceBase64Images($data['content'], 'products');
        }

        return $this->products->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        if (isset($data['content'])) {
            $data['content'] = $this->contentImages->replaceBase64Images($data['content'], 'products');
        }

        $this->products->update($product->id, $data);

        return $product->refresh();
    }

    public function delete(Product $product): bool
    {
        return $this->products->delete($product->id);
    }
}
