<?php

namespace App\Models\Concerns;

use App\Models\RichEditorMedia;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait HasRichEditorMedia
{
    protected array $richEditorOriginalContent = [];

    protected int $richEditorMaxImageSize = 5_000_000;

    public static function bootHasRichEditorMedia(): void
    {
        static::updating(function ($model) {
            $model->snapshotRichEditorContent();
        });

        static::saving(function ($model) {
            $model->convertBase64ImagesToFiles();
        });

        static::saved(function ($model) {
            $model->cleanupRemovedRichEditorImages();
            $model->syncRichEditorMedia();
        });

        static::deleted(function ($model) {
            if (method_exists($model, 'isForceDeleting')) {
                return;
            }
            $model->deleteAllRichEditorImages();
            $model->richEditorMedia()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::forceDeleted(function ($model) {
                $model->deleteAllRichEditorImages();
                $model->richEditorMedia()->delete();
            });
        }
    }

    public function richEditorMedia(): MorphMany
    {
        return $this->morphMany(RichEditorMedia::class, 'model');
    }

    protected function syncRichEditorMedia(): void
    {
        $richEditorFields = $this->getRichEditorFields();

        foreach ($richEditorFields as $fieldName) {
            $content = $this->getAttribute($fieldName);

            if (!$content) {
                $this->richEditorMedia()->where('field_name', $fieldName)->delete();
                continue;
            }

            $imagePaths = $this->extractImagePathsFromContent($content);

            $existingMedia = $this->richEditorMedia()
                ->where('field_name', $fieldName)
                ->pluck('file_path')
                ->toArray();

            $mediaToDelete = array_diff($existingMedia, $imagePaths);
            $mediaToAdd = array_diff($imagePaths, $existingMedia);

            $this->richEditorMedia()
                ->where('field_name', $fieldName)
                ->whereIn('file_path', $mediaToDelete)
                ->delete();

            foreach ($mediaToAdd as $imagePath) {
                $this->richEditorMedia()->create([
                    'field_name' => $fieldName,
                    'file_path' => $imagePath,
                    'disk' => 'public',
                ]);
            }
        }
    }

    protected function extractImagePathsFromContent(string $content): array
    {
        $paths = [];

        // Lexical JSON format - parse "src" from image nodes
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $this->collectImageSrcFromLexical($decoded, $paths);
        }

        // HTML img src
        preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $src) {
                $relative = $this->normalizeStoragePath($src);
                if ($relative) {
                    $paths[] = $relative;
                }
            }
        }

        // data-url attr
        preg_match_all('/data-url=[\'"]([^\'"]+)[\'"]/', $content, $dataMatches);
        if (!empty($dataMatches[1])) {
            foreach ($dataMatches[1] as $src) {
                $relative = $this->normalizeStoragePath($src);
                if ($relative) {
                    $paths[] = $relative;
                }
            }
        }

        // JSON "url":"..."
        if (preg_match_all('/"url":"([^"]+)"/', $content, $jsonMatches)) {
            foreach ($jsonMatches[1] as $src) {
                $relative = $this->normalizeStoragePath($src);
                if ($relative) {
                    $paths[] = $relative;
                }
            }
        }

        return array_unique($paths);
    }

    protected function collectImageSrcFromLexical(array $node, array &$paths): void
    {
        if (($node['type'] ?? null) === 'image' && isset($node['src']) && is_string($node['src'])) {
            $relative = $this->normalizeStoragePath($node['src']);
            if ($relative) {
                $paths[] = $relative;
            }
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                if (is_array($child)) {
                    $this->collectImageSrcFromLexical($child, $paths);
                }
            }
        }

        if (isset($node['caption']) && is_array($node['caption'])) {
            $this->collectImageSrcFromLexical($node['caption'], $paths);
        }

        if (isset($node['root']) && is_array($node['root'])) {
            $this->collectImageSrcFromLexical($node['root'], $paths);
        }
    }

    protected function getRichEditorFields(): array
    {
        return property_exists($this, 'richEditorFields')
            ? $this->richEditorFields
            : [];
    }

    protected function convertBase64ImagesToFiles(): void
    {
        $richEditorFields = $this->getRichEditorFields();

        foreach ($richEditorFields as $fieldName) {
            $content = $this->getAttribute($fieldName);

            if (!$content) {
                continue;
            }

            $content = $this->convertBase64ToStorage($content);
            $this->setAttribute($fieldName, $content);
        }
    }

    protected function convertBase64ToStorage(string $content): string
    {
        // Try Lexical JSON format first
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $hasChanges = false;
            $cache = [];
            $decoded = $this->processLexicalNode($decoded, $hasChanges, $cache);
            if ($hasChanges) {
                return json_encode($decoded);
            }
            return $content;
        }

        // Fall back to HTML base64 regex
        preg_match_all(
            '/data:image\/(png|jpg|jpeg|gif|webp|svg\+xml);base64,([A-Za-z0-9+\/=]+)/i',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches)) {
            return $content;
        }

        foreach ($matches as $match) {
            $fullBase64 = $match[0];
            $extension = $match[1] === 'svg+xml' ? 'svg' : $match[1];
            $base64Data = $match[2];

            try {
                $filePath = $this->saveBase64AsFile($base64Data, $extension);
                $fileUrl = $this->richEditorPublicUrl($filePath);
                $content = str_replace($fullBase64, $fileUrl, $content);

                Log::info(sprintf(
                    'Converted base64 image for %s:%s -> %s',
                    static::class,
                    $this->getKey() ?? 'new',
                    $filePath
                ));
            } catch (\Exception $e) {
                Log::error('Failed to convert base64 image: ' . $e->getMessage());
                continue;
            }
        }

        return $content;
    }

    protected function processLexicalNode(array $node, bool &$hasChanges, array &$cache): array
    {
        if ($this->isImageNodeWithBase64($node)) {
            $key = md5($node['src']);
            if (!isset($cache[$key])) {
                $storedUrl = $this->storeDataUrl($node['src']);
                if ($storedUrl) {
                    $cache[$key] = $storedUrl;
                }
            }

            if (isset($cache[$key])) {
                $node['src'] = $cache[$key];
                $hasChanges = true;
            }
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $index => $child) {
                if (is_array($child)) {
                    $node['children'][$index] = $this->processLexicalNode($child, $hasChanges, $cache);
                }
            }
        }

        if (isset($node['caption']) && is_array($node['caption'])) {
            $node['caption'] = $this->processLexicalNode($node['caption'], $hasChanges, $cache);
        }

        if (isset($node['root']) && is_array($node['root'])) {
            $node['root'] = $this->processLexicalNode($node['root'], $hasChanges, $cache);
        }

        return $node;
    }

    protected function isImageNodeWithBase64(array $node): bool
    {
        return ($node['type'] ?? null) === 'image'
            && isset($node['src'])
            && is_string($node['src'])
            && str_starts_with($node['src'], 'data:image/');
    }

    protected function storeDataUrl(string $dataUrl): ?string
    {
        if (!preg_match('/^data:(image\/[^;]+);base64,(.+)$/', $dataUrl, $matches)) {
            return null;
        }

        $mime = $matches[1];
        $base64 = $matches[2];
        $binary = base64_decode($base64, true);

        if ($binary === false || strlen($binary) > $this->richEditorMaxImageSize) {
            return null;
        }

        $mimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/avif' => 'avif',
        ];

        $extension = $mimes[$mime] ?? 'png';
        $filename = $this->richEditorFilenamePrefix() . '-' . time() . '-' . Str::random(8) . '.' . $extension;

        $baseDir = $this->richEditorContentDirectory();
        $datedDir = $baseDir . '/' . now()->format('Y/m/d');
        $path = trim($datedDir, '/') . '/' . $filename;

        $disk = Storage::disk($this->richEditorDisk());
        $directory = dirname($path);

        if (!$disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $saved = $disk->put($path, $binary);

        if (!$saved) {
            return null;
        }

        Log::info(sprintf('Stored base64 image: %s', $path));

        return $this->richEditorPublicUrl($path);
    }

    protected function saveBase64AsFile(string $base64Data, string $extension): string
    {
        $imageData = base64_decode($base64Data);

        if ($imageData === false) {
            throw new \Exception('Failed to decode base64 data');
        }

        if (strlen($imageData) > $this->richEditorMaxImageSize) {
            throw new \Exception('Image exceeds maximum size of 5MB');
        }

        $baseDir = $this->richEditorContentDirectory();
        $datedDir = $baseDir . '/' . now()->format('Y/m/d');
        $filename = $this->richEditorFilenamePrefix() . '-' . time() . '-' . Str::random(8) . '.' . $extension;
        $path = trim($datedDir, '/') . '/' . $filename;

        $disk = Storage::disk($this->richEditorDisk());
        $directory = dirname($path);

        if (!$disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $saved = $disk->put($path, $imageData);

        if (!$saved) {
            throw new \Exception('Failed to save file to storage');
        }

        return $path;
    }

    protected function deleteImageFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $relativePath = ltrim($relativePath, '/');
        $disk = Storage::disk($this->richEditorDisk());

        try {
            if ($disk->exists($relativePath)) {
                $disk->delete($relativePath);
                Log::info(sprintf('Deleted rich-editor image: %s', $relativePath));
            }
        } catch (\Exception $e) {
            Log::error(sprintf('Error deleting image %s: %s', $relativePath, $e->getMessage()));
        }
    }

    protected function richEditorPublicUrl(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');

        if ($this->richEditorDisk() === 'public' && config('filesystems.disks.public.driver') === 'local') {
            return '/storage/' . $relativePath;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->richEditorDisk());

        return $disk->url($relativePath);
    }

    protected function normalizeStoragePath(string $url): ?string
    {
        // Skip base64 data URLs
        if (str_starts_with($url, 'data:')) {
            return null;
        }

        $url = str_replace(config('app.url'), '', $url);
        $url = str_replace(url('/'), '', $url);
        $url = ltrim($url, '/');

        if (str_starts_with($url, 'storage/')) {
            $url = substr($url, strlen('storage/'));
        }

        if (str_starts_with($url, 'uploads/') || str_starts_with($url, 'posts/')) {
            return $url;
        }

        return null;
    }

    protected function snapshotRichEditorContent(): void
    {
        $this->richEditorOriginalContent = [];

        foreach ($this->getRichEditorFields() as $fieldName) {
            $this->richEditorOriginalContent[$fieldName] = $this->getOriginal($fieldName);
        }
    }

    protected function cleanupRemovedRichEditorImages(): void
    {
        if (empty($this->richEditorOriginalContent)) {
            return;
        }

        foreach ($this->getRichEditorFields() as $fieldName) {
            $oldContent = $this->richEditorOriginalContent[$fieldName] ?? null;
            $newContent = $this->getAttribute($fieldName);

            if (!$oldContent) {
                continue;
            }

            $oldImages = $this->extractImagePathsFromContent($oldContent);
            $newImages = $this->extractImagePathsFromContent($newContent ?? '');

            $imagesToDelete = array_diff($oldImages, $newImages);

            foreach ($imagesToDelete as $relativePath) {
                $this->deleteImageFile($relativePath);
            }
        }

        $this->richEditorOriginalContent = [];
    }

    protected function deleteAllRichEditorImages(): void
    {
        foreach ($this->getRichEditorFields() as $fieldName) {
            $content = $this->getAttribute($fieldName);
            if (!$content) {
                continue;
            }

            $images = $this->extractImagePathsFromContent($content);
            foreach ($images as $relativePath) {
                $this->deleteImageFile($relativePath);
            }
        }
    }

    protected function richEditorFilenamePrefix(): string
    {
        if (method_exists($this, 'mediaPlaceholderKey')) {
            return $this->mediaPlaceholderKey() . '-lexical';
        }

        return 'lexical';
    }

    protected function richEditorContentDirectory(): string
    {
        if (property_exists($this, 'richEditorContentDirectory')) {
            return trim($this->richEditorContentDirectory, '/');
        }

        if (method_exists($this, 'mediaPlaceholderKey')) {
            return 'uploads/' . Str::plural($this->mediaPlaceholderKey()) . '/content';
        }

        return 'uploads/rich-editor/content';
    }

    protected function richEditorDisk(): string
    {
        return property_exists($this, 'richEditorDisk')
            ? (string) $this->richEditorDisk
            : 'public';
    }
}
