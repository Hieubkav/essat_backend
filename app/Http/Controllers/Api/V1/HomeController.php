<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HomeComponentType;
use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\HomeComponent;
use App\Models\Menu;
use App\Models\Post;
use App\Models\Product;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(private SettingService $settingService)
    {
    }

    /**
     * Single endpoint trả về tất cả data cho trang chủ.
     * Giảm từ 9 API calls xuống còn 1.
     */
    public function index(): JsonResponse
    {
        $data = Cache::remember('home-page-data', 3600, function () {
            return [
                'settings' => $this->getSettings(),
                'menus' => $this->getMenus(),
                'components' => $this->getComponents(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    protected function getSettings(): array
    {
        $setting = $this->settingService->get();
        return (new SettingResource($setting))->resolve();
    }

    protected function getMenus(): array
    {
        return Menu::query()
            ->root()
            ->active()
            ->ordered()
            ->with(['children' => fn ($query) => $query->active()->ordered()])
            ->get()
            ->map(fn (Menu $menu) => $this->transformMenu($menu))
            ->toArray();
    }

    protected function transformMenu(Menu $menu): array
    {
        $result = [
            'label' => $menu->name,
            'href' => $menu->resolved_url,
        ];

        if ($menu->children->isNotEmpty()) {
            $result['children'] = $menu->children->map(fn (Menu $child) => [
                'label' => $child->name,
                'href' => $child->resolved_url,
            ])->toArray();
        }

        return $result;
    }

    protected function getComponents(): array
    {
        return HomeComponent::query()
            ->where('active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->type => $this->transformConfig($item->type, $item->config ?? []),
            ])
            ->toArray();
    }

    protected function transformConfig(string $type, array $config): array
    {
        $config = $this->transformImagePaths($config);

        if ($type === HomeComponentType::FeaturedProducts->value) {
            $config = $this->transformFeaturedProducts($config);
        }

        if ($type === HomeComponentType::News->value) {
            $config = $this->transformNews($config);
        }

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
                    'image' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
                    'name' => $product->name,
                    'price' => $product->price ? number_format($product->price, 0, ',', '.') . ' đ' : 'Liên hệ',
                    'link' => '/products/' . $product->slug,
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
                    'image' => $post->thumbnail ? asset('storage/' . $post->thumbnail) : null,
                    'title' => $post->title,
                    'link' => '/news/' . $post->slug,
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
            } elseif (in_array($key, $imageFields) && is_string($value) && !empty($value)) {
                if (!str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
                    $data[$key] = asset('storage/' . $value);
                }
            }
        }

        return $data;
    }
}
