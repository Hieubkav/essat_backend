<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Helpers\PaginatedResponse;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function __construct(private CategoryService $categoryService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $categories = $this->categoryService->listActive($perPage);

        return $this->success(
            PaginatedResponse::makeWithLinks(CategoryResource::collection($categories)),
            'Categories retrieved successfully'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $category = $this->categoryService->findBySlug($slug);

        if (!$category) {
            return $this->notFound('Category not found');
        }

        return $this->success(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }
}
