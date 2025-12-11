<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function updateMedia(Media $media, array $data): Media;

    public function deleteMany(array $ids): int;
}
