<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ProductCategoryResource;
use App\Services\ProductCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends ApiController
{
    public function __construct(private ProductCategoryService $categoryService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $categories = $this->categoryService->listActive($perPage);

        return $this->success(
            ProductCategoryResource::collection($categories),
            'Product categories retrieved successfully'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $category = $this->categoryService->findBySlug($slug);

        if (!$category) {
            return $this->notFound('Product category not found');
        }

        return $this->success(
            new ProductCategoryResource($category),
            'Product category retrieved successfully'
        );
    }
}
