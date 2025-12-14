<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Helpers\PaginatedResponse;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function __construct(private ProductService $productService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $categoryId = $request->get('category_id');

        $products = $this->productService->listActive($perPage, $categoryId);

        return $this->success(
            PaginatedResponse::makeWithLinks(ProductResource::collection($products)),
            'Products retrieved successfully'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->productService->findBySlug($slug);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        $product->load('categories');
        $relatedProducts = $this->productService->getRelated($product, 8);

        return $this->success(
            (new ProductResource($product))->withRelated($relatedProducts),
            'Product retrieved successfully'
        );
    }

    public function featured(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 8);
        $products = $this->productService->getFeatured($limit);

        return $this->success(
            ProductResource::collection($products),
            'Featured products retrieved successfully'
        );
    }
}
