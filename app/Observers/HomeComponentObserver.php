<?php

namespace App\Observers;

use App\Models\HomeComponent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;

class HomeComponentObserver
{
    protected array $imageFields = ['image', 'logo', 'avatar', 'thumbnail'];

    protected array $convertibleExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    public function saving(HomeComponent $component): void
    {
        $this->processImages($component);
    }

    public function updating(HomeComponent $component): void
    {
        $this->cleanupOldImages($component);
    }

    public function saved(HomeComponent $component): void
    {
        $this->clearCache($component);
    }

    public function deleted(HomeComponent $component): void
    {
        $this->clearCache($component);
        $this->deleteAllImages($component);
    }

    protected function clearCache(HomeComponent $component): void
    {
        Cache::forget('home-components');
        Cache::forget("home-component-{$component->type}");
    }

    protected function processImages(HomeComponent $component): void
    {
        $config = $component->config ?? [];
        $config = $this->convertImagesToWebp($config);
        $component->config = $config;
    }

    protected function convertImagesToWebp(array $data, string $path = ''): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->convertImagesToWebp($value, $path . $key . '.');
            } elseif (in_array($key, $this->imageFields) && is_string($value) && !empty($value)) {
                $data[$key] = $this->convertSingleImageToWebp($value);
            }
        }

        return $data;
    }

    protected function convertSingleImageToWebp(string $filePath): string
    {
        if (!Storage::disk('public')->exists($filePath)) {
            return $filePath;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->convertibleExtensions)) {
            return $filePath;
        }

        if ($extension === 'webp') {
            return $filePath;
        }

        $fullPath = Storage::disk('public')->path($filePath);
        $newFileName = pathinfo($filePath, PATHINFO_FILENAME) . '.webp';
        $newFilePath = dirname($filePath) . '/' . $newFileName;
        $newFullPath = Storage::disk('public')->path($newFilePath);

        try {
            Image::useImageDriver(ImageDriver::Gd)
                ->load($fullPath)
                ->quality(80)
                ->save($newFullPath);

            if (file_exists($fullPath) && $fullPath !== $newFullPath) {
                unlink($fullPath);
            }

            return $newFilePath;
        } catch (\Exception $e) {
            report($e);
            return $filePath;
        }
    }

    protected function cleanupOldImages(HomeComponent $component): void
    {
        $originalConfig = $component->getOriginal('config') ?? [];
        $newConfig = $component->config ?? [];

        $oldImages = $this->extractAllImages($originalConfig);
        $newImages = $this->extractAllImages($newConfig);

        $imagesToDelete = array_diff($oldImages, $newImages);

        foreach ($imagesToDelete as $imagePath) {
            $this->deleteImage($imagePath);
        }
    }

    protected function deleteAllImages(HomeComponent $component): void
    {
        $config = $component->config ?? [];
        $images = $this->extractAllImages($config);

        foreach ($images as $imagePath) {
            $this->deleteImage($imagePath);
        }
    }

    protected function extractAllImages(array $data): array
    {
        $images = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $images = array_merge($images, $this->extractAllImages($value));
            } elseif (in_array($key, $this->imageFields) && is_string($value) && !empty($value)) {
                if (!str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
                    $images[] = $value;
                }
            }
        }

        return $images;
    }

    protected function deleteImage(string $filePath): void
    {
        if (empty($filePath)) {
            return;
        }

        if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
            return;
        }

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }
}
