<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HomeComponent;
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
                    'config' => $this->transformConfig($item->config ?? []),
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

        if (!$component) {
            return response()->json([
                'success' => false,
                'message' => 'Component not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $component->type,
                'config' => $this->transformConfig($component->config ?? []),
            ],
        ]);
    }

    protected function transformConfig(array $config): array
    {
        return $this->transformImagePaths($config);
    }

    protected function transformImagePaths(array $data): array
    {
        $imageFields = ['image', 'logo', 'avatar', 'thumbnail'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->transformImagePaths($value);
            } elseif (in_array($key, $imageFields) && is_string($value) && !empty($value)) {
                $data[$key] = asset('storage/' . $value);
            }
        }

        return $data;
    }
}
