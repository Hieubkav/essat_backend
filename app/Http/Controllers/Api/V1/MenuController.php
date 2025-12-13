<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        $menus = Cache::remember('navigation-menus', 3600, function () {
            return Menu::query()
                ->root()
                ->active()
                ->ordered()
                ->with(['children' => fn ($query) => $query->active()->ordered()])
                ->get()
                ->map(fn (Menu $menu) => $this->transformMenu($menu));
        });

        return response()->json([
            'success' => true,
            'data' => $menus,
        ]);
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
}
