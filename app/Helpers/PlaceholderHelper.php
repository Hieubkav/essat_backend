<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PlaceholderHelper
{
    private const CACHE_KEY = 'settings.placeholder_url';

    private const CACHE_TTL = 3600; // 1 hour

    public static function getUrl(): ?string
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $setting = Setting::where('singleton', Setting::SINGLETON_KEY)->first();

            return $setting?->placeholder
                ? Storage::disk('public')->url($setting->placeholder)
                : null;
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
