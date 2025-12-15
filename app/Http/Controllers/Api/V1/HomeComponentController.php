<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HomeComponentType;
use App\Helpers\PlaceholderHelper;
use App\Http\Controllers\Controller;
use App\Models\HomeComponent;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeComponentController extends Controller
{
    public function index(): JsonResponse
    {
        $components = Cache::remember('home-components', 3600, function () {
            return HomeComponent::query()
                ->where('active', true)
                ->orderBy('order', 'asc')
                ->get()
                ->map(fn ($item) => [
                    'type' => $item->type,
                    'config' => $this->transformConfig($item->type, $item->config ?? []),
                ]);
        });

        return response()->json([
            'success' => true,
            'data' => $components,
        ]);
    }

    public function show(string $type): JsonResponse
    {
        $component = Cache::remember("home-component-{$type}", 3600, function () use ($type) {
            return HomeComponent::query()
                ->where('type', $type)
                ->where('active', true)
                ->first();
        });

        if (! $component) {
            return response()->json([
                'success' => false,
                'message' => 'Component not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $component->type,
                'config' => $this->transformConfig($component->type, $component->config ?? []),
            ],
        ]);
    }

    protected function transformConfig(string $type, array $config): array
    {
        $config = $this->transformImagePaths($config);

        if ($type === HomeComponentType::ProductCategories->value) {
            $config = $this->transformProductCategories($config);
        }

        if ($type === HomeComponentType::FeaturedProducts->value) {
            $config = $this->transformFeaturedProducts($config);
        }

        if ($type === HomeComponentType::News->value) {
            $config = $this->transformNews($config);
        }

        return $config;
    }

    protected function transformProductCategories(array $config): array
    {
        if (empty($config['categories'])) {
            return $config;
        }

        // Lấy tất cả category_ids cần query
        $categoryIds = collect($config['categories'])
            ->where('link_type', 'category')
            ->pluck('category_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Query một lần để lấy tất cả slugs
        $categorySlugs = [];
        if (! empty($categoryIds)) {
            $categorySlugs = \App\Models\ProductCategory::query()
                ->whereIn('id', $categoryIds)
                ->pluck('slug', 'id')
                ->toArray();
        }

        // Transform categories với link đúng
        $config['categories'] = array_map(function ($category) use ($categorySlugs) {
            $linkType = $category['link_type'] ?? 'custom';

            if ($linkType === 'category' && ! empty($category['category_id'])) {
                $slug = $categorySlugs[$category['category_id']] ?? null;
                $category['link'] = $slug ? '/san-pham?category='.$slug : '#';
            }

            // Đảm bảo luôn có link
            if (empty($category['link'])) {
                $category['link'] = '#';
            }

            // Cleanup: không cần trả về link_type và category_id cho frontend
            unset($category['link_type'], $category['category_id']);

            return $category;
        }, $config['categories']);

        return $config;
    }

    protected function transformFeaturedProducts(array $config): array
    {
        $displayMode = $config['display_mode'] ?? 'manual';

        if ($displayMode === 'latest') {
            $limit = (int) ($config['limit'] ?? 8);
            $products = Product::query()
                ->where('active', true)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (Product $product) => [
                    'image' => $product->thumbnail
                        ? asset('storage/'.$product->thumbnail)
                        : PlaceholderHelper::getUrl(),
                    'name' => $product->name,
                    'price' => $product->price ? number_format($product->price, 0, ',', '.').' đ' : 'Liên hệ',
                    'link' => '/san-pham/'.$product->slug,
                ])
                ->toArray();

            $config['products'] = $products;
        }

        return $config;
    }

    protected function transformNews(array $config): array
    {
        $displayMode = $config['display_mode'] ?? 'manual';

        if ($displayMode === 'latest') {
            $limit = (int) ($config['limit'] ?? 6);
            $posts = Post::query()
                ->where('active', true)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (Post $post) => [
                    'image' => $post->thumbnail ? asset('storage/'.$post->thumbnail) : null,
                    'title' => $post->title,
                    'link' => '/bai-viet/'.$post->slug,
                ])
                ->toArray();

            $config['posts'] = $posts;
        }

        return $config;
    }

    protected function transformImagePaths(array $data): array
    {
        $imageFields = ['image', 'logo', 'avatar', 'thumbnail'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->transformImagePaths($value);
            } elseif (in_array($key, $imageFields) && is_string($value) && ! empty($value)) {
                // Chỉ transform nếu là local path (không phải URL đầy đủ)
                if (! str_starts_with($value, 'http://') && ! str_starts_with($value, 'https://')) {
                    $data[$key] = asset('storage/'.$value);
                }
            }
        }

        return $data;
    }
}
