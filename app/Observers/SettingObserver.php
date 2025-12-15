<?php

namespace App\Observers;

use App\Helpers\PlaceholderHelper;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingObserver
{
    protected array $imageFields = ['logo', 'favicon', 'placeholder'];

    public function updating(Setting $setting): void
    {
        foreach ($this->imageFields as $field) {
            $oldPath = $setting->getOriginal($field);
            $newPath = $setting->getAttribute($field);

            if ($oldPath && $oldPath !== $newPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }
    }

    public function updated(Setting $setting): void
    {
        if ($setting->wasChanged('placeholder')) {
            PlaceholderHelper::clearCache();
        }
    }

    public function deleted(Setting $setting): void
    {
        foreach ($this->imageFields as $field) {
            $path = $setting->getAttribute($field);

            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
