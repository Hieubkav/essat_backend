<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicPostController extends ApiController
{
    public function __construct(private PostService $postService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $categoryId = $request->get('category_id');

        $posts = $this->postService->listActive($perPage, $categoryId);

        return $this->success(
            PostResource::collection($posts),
            'Posts retrieved successfully'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $post = $this->postService->findBySlug($slug);

        if (!$post) {
            return $this->notFound('Post not found');
        }

        $post->load('category');

        return $this->success(
            new PostResource($post),
            'Post retrieved successfully'
        );
    }

    public function latest(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 6);
        $posts = $this->postService->getLatest($limit);

        return $this->success(
            PostResource::collection($posts),
            'Latest posts retrieved successfully'
        );
    }
}
