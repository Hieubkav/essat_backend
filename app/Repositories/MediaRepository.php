<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRepository extends BaseRepository implements MediaRepositoryInterface
{
    public function __construct()
    {
        $this->model = new Media();
    }

    /**
     * Danh sách media với bộ lọc cơ bản.
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->orderByDesc('id');

        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        if ($mime = $filters['mime_type'] ?? null) {
            $query->where('mime_type', 'like', "{$mime}%");
        }

        if ($collection = $filters['collection_name'] ?? null) {
            $query->where('collection_name', $collection);
        }

        if ($modelType = $filters['model_type'] ?? null) {
            $query->where('model_type', $modelType);
        }

        if ($modelId = $filters['model_id'] ?? null) {
            $query->where('model_id', $modelId);
        }

        if ($from = $filters['from'] ?? null) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $filters['to'] ?? null) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->paginate($perPage);
    }

    /**
     * Cập nhật metadata.
     */
    public function updateMedia(Media $media, array $data): Media
    {
        if (isset($data['name'])) {
            $media->name = $data['name'];
        }

        if (isset($data['title'])) {
            $media->setCustomProperty('title', $data['title']);
        }

        if (isset($data['alt'])) {
            $media->setCustomProperty('alt', $data['alt']);
        }

        $media->save();

        return $media->refresh();
    }

    /**
     * Xóa nhiều media.
     */
    public function deleteMany(array $ids): int
    {
        return $this->model->newQuery()->whereIn('id', $ids)->delete();
    }
}
