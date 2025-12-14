<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Support\Str;

class PostService extends BaseService
{
    public function __construct(
        private PostRepository $posts,
        private ContentImageService $contentImages,
    )
    {
    }

    /**
     * Get paginated posts.
     */
    public function list(int $perPage = 15)
    {
        return $this->posts->paginate($perPage);
    }

    /**
     * Get active posts (public API).
     */
    public function listActive(int $perPage = 15, ?int $categoryId = null)
    {
        return $this->posts->getActive($perPage, $categoryId);
    }

    /**
     * Find post by slug (public API).
     */
    public function findBySlug(string $slug): ?Post
    {
        return $this->posts->findActiveBySlug($slug);
    }

    /**
     * Get latest posts.
     */
    public function getLatest(int $limit = 6)
    {
        return $this->posts->getLatest($limit);
    }

    /**
     * Get related posts by category.
     */
    public function getRelatedPosts(Post $post, int $limit = 6)
    {
        if (!$post->category_id) {
            return collect();
        }

        return $this->posts->getRelatedByCategory($post->category_id, $post->id, $limit);
    }

    /**
     * Create a new post.
     */
    public function create(array $data): Post
    {
        $data['slug'] ??= Str::slug($data['title']);
        if (isset($data['content'])) {
            $data['content'] = $this->contentImages->replaceBase64Images($data['content'], 'posts');
        }

        return $this->posts->create($data);
    }

    /**
     * Get a post by id.
     */
    public function find(int|string $id): Post
    {
        return $this->posts->findOrFail($id);
    }

    /**
     * Update a post.
     */
    public function update(Post $post, array $data): Post
    {
        if (isset($data['content'])) {
            $data['content'] = $this->contentImages->replaceBase64Images($data['content'], 'posts');
        }

        $this->posts->update($post->id, $data);

        return $post->refresh();
    }

    /**
     * Delete a post.
     */
    public function delete(Post $post): bool
    {
        return $this->posts->delete($post->id);
    }
}
