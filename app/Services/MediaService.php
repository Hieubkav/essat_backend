<?php

namespace App\Services;

use App\Models\MediaLibrary;
use App\Repositories\MediaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService extends BaseService
{
    public function __construct(
        private MediaRepository $mediaRepository
    ) {
    }

    /**
     * Danh sách media có phân trang + filter.
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->mediaRepository->paginateWithFilters($filters, $perPage);
    }

    /**
     * Upload media vào bucket của user hiện tại.
     */
    public function upload(array $data): Media
    {
        $user = auth()->user();
        $bucket = MediaLibrary::firstOrCreate(['user_id' => $user?->id]);
        $file = $data['file'];

        $collection = $data['collection_name'] ?? 'library';
        $displayName = $data['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($displayName) ?: Str::random(8);
        $extension = $file->getClientOriginalExtension() ?: 'file';

        $adder = $bucket->addMedia($file)
            ->usingName($displayName)
            ->usingFileName("{$safeName}.{$extension}")
            ->withCustomProperties([
                'title' => $data['title'] ?? null,
                'alt' => $data['alt'] ?? null,
                'uploaded_by' => $user?->id,
            ]);

        return $adder->toMediaCollection($collection, 'public');
    }

    /**
     * Cập nhật metadata của media.
     */
    public function update(Media $media, array $data): Media
    {
        return $this->mediaRepository->updateMedia($media, $data);
    }

    /**
     * Xóa media (KISS: giao cho Spatie lo việc dọn file).
     */
    public function delete(Media $media): bool
    {
        $id = $media->getKey();

        try {
            // Đảm bảo giá trị disk không null cho URL generator
            $defaultDisk = config('media-library.disk_name', 'public');
            $media->disk = $media->disk ?: $defaultDisk;
            $media->conversions_disk = $media->conversions_disk ?: $media->disk;

            // Xóa bằng observer của Spatie
            $media->delete();

            if (Media::whereKey($id)->exists()) {
                \Log::warning('Media delete did not remove record', ['id' => $id]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            \Log::error('Media delete exception', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
