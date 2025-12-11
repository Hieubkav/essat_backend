<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Chuyển các ảnh base64 trong nội dung Lexical sang file trong storage
 * rồi thay thế src bằng URL public.
 */
class ContentImageService extends BaseService
{
    /**
     * Các MIME type ảnh được chấp nhận.
     *
     * @var array<string, string>
     */
    private const SUPPORTED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'image/avif' => 'avif',
    ];

    /**
     * Lấy danh sách URL ảnh trong nội dung Lexical (đã encode JSON).
     *
     * @return array<int, string>
     */
    public function extractImageUrls(string $content): array
    {
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return [];
        }

        $urls = [];
        $this->collectImageSrc($decoded, $urls);

        return array_values(array_unique($urls));
    }

    /**
     * Thay thế ảnh base64 trong nội dung Lexical bằng link file trong storage.
     */
    public function replaceBase64Images(string $content, string $directory = 'posts'): string
    {
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return $content;
        }

        $hasChanges = false;
        $cache = [];
        $decoded = $this->processNode($decoded, $directory, $hasChanges, $cache);

        if (!$hasChanges) {
            return $content;
        }

        return json_encode($decoded);
    }

    /**
     * Xóa các ảnh đã bị loại bỏ khi so sánh content cũ và mới.
     *
     * @param array<int, string> $allowedDirectories
     */
    public function deleteRemovedImages(string $oldContent, string $newContent, array $allowedDirectories = ['posts']): void
    {
        $old = $this->extractImageUrls($oldContent);
        $new = $this->extractImageUrls($newContent);

        $removed = array_diff($old, $new);
        $this->deleteImagesByUrls($removed, $allowedDirectories);
    }

    /**
     * Xóa toàn bộ ảnh trong content (dùng khi xóa record).
     *
     * @param array<int, string> $allowedDirectories
     */
    public function deleteAllImages(string $content, array $allowedDirectories = ['posts']): void
    {
        $urls = $this->extractImageUrls($content);
        $this->deleteImagesByUrls($urls, $allowedDirectories);
    }

    /**
     * Duyệt cây node của Lexical và xử lý ảnh.
     *
     * @param array<string, mixed> $node
     * @param array<string, string> $cache
     */
    private function processNode(array $node, string $directory, bool &$hasChanges, array &$cache): array
    {
        if ($this->isImageNodeWithBase64($node)) {
            $key = md5($node['src']);
            $storedUrl = $cache[$key] ?? $this->storeDataUrl($node['src'], $directory);

            if ($storedUrl) {
                $cache[$key] = $storedUrl;
                $node['src'] = $storedUrl;
                $hasChanges = true;
            }
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $index => $child) {
                if (is_array($child)) {
                    $node['children'][$index] = $this->processNode($child, $directory, $hasChanges, $cache);
                }
            }
        }

        if (isset($node['caption']) && is_array($node['caption'])) {
            $node['caption'] = $this->processNode($node['caption'], $directory, $hasChanges, $cache);
        }

        if (isset($node['root']) && is_array($node['root'])) {
            $node['root'] = $this->processNode($node['root'], $directory, $hasChanges, $cache);
        }

        return $node;
    }

    /**
     * Gom các src ảnh trong cây node.
     *
     * @param array<string, mixed> $node
     * @param array<int, string>   $urls
     */
    private function collectImageSrc(array $node, array &$urls): void
    {
        if (($node['type'] ?? null) === 'image' && isset($node['src']) && is_string($node['src'])) {
            $urls[] = $node['src'];
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                if (is_array($child)) {
                    $this->collectImageSrc($child, $urls);
                }
            }
        }

        if (isset($node['caption']) && is_array($node['caption'])) {
            $this->collectImageSrc($node['caption'], $urls);
        }

        if (isset($node['root']) && is_array($node['root'])) {
            $this->collectImageSrc($node['root'], $urls);
        }
    }

    /**
     * Kiểm tra node ảnh có src dạng base64 không.
     *
     * @param array<string, mixed> $node
     */
    private function isImageNodeWithBase64(array $node): bool
    {
        return ($node['type'] ?? null) === 'image'
            && isset($node['src'])
            && is_string($node['src'])
            && str_starts_with($node['src'], 'data:image/');
    }

    /**
     * Lưu data URL ảnh vào storage/public và trả về URL public.
     */
    private function storeDataUrl(string $dataUrl, string $directory): ?string
    {
        if (!preg_match('/^data:(image\\/[^;]+);base64,(.+)$/', $dataUrl, $matches)) {
            return null;
        }

        $mime = $matches[1];
        $base64 = $matches[2];
        $binary = base64_decode($base64, true);

        if ($binary === false) {
            return null;
        }

        $extension = self::SUPPORTED_MIMES[$mime] ?? 'png';
        $fileName = Str::uuid()->toString() . '.' . $extension;
        $path = trim($directory, '/') . '/' . $fileName;

        Storage::disk('public')->put($path, $binary);

        return Storage::disk('public')->url($path);
    }

    /**
     * Xóa file theo danh sách URL public.
     *
     * @param iterable<string> $urls
     * @param array<int, string> $allowedDirectories
     */
    private function deleteImagesByUrls(iterable $urls, array $allowedDirectories): void
    {
        foreach ($urls as $url) {
            if (!is_string($url)) {
                continue;
            }

            $path = $this->urlToPublicPath($url, $allowedDirectories);
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Chuyển URL public về path trong disk public và kiểm soát thư mục cho phép.
     */
    private function urlToPublicPath(string $url, array $allowedDirectories): ?string
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['path'])) {
            return null;
        }

        $path = ltrim($parsed['path'], '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        foreach ($allowedDirectories as $dir) {
            $dir = trim($dir, '/');
            if ($dir === '' || str_starts_with($path, $dir . '/')) {
                return $path;
            }
        }

        return null;
    }
}
