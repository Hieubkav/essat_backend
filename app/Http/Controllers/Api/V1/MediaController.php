<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Media\MediaIndexRequest;
use App\Http\Requests\Media\MediaStoreRequest;
use App\Http\Requests\Media\MediaUpdateRequest;
use App\Http\Resources\MediaResource;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends ApiController
{
    public function __construct(private MediaService $mediaService)
    {
    }

    /**
     * Danh sách media.
     */
    public function index(MediaIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);
        unset($filters['per_page']);

        $media = $this->mediaService->paginate($filters, $perPage);

        return $this->success(
            MediaResource::collection($media),
            'Media retrieved successfully'
        );
    }

    /**
     * Upload media mới.
     */
    public function store(MediaStoreRequest $request): JsonResponse
    {
        $media = $this->mediaService->upload($request->validated());

        return $this->created(
            new MediaResource($media),
            'Media uploaded successfully'
        );
    }

    /**
     * Xem chi tiết media.
     */
    public function show(Media $medium): JsonResponse
    {
        return $this->success(
            new MediaResource($medium),
            'Media retrieved successfully'
        );
    }

    /**
     * Cập nhật metadata media.
     */
    public function update(MediaUpdateRequest $request, Media $medium): JsonResponse
    {
        $updated = $this->mediaService->update($medium, $request->validated());

        return $this->success(
            new MediaResource($updated),
            'Media updated successfully'
        );
    }

    /**
     * Xóa media.
     */
    public function destroy(Media $medium): JsonResponse
    {
        $deleted = $this->mediaService->delete($medium);

        if (!$deleted) {
            throw new ApiException('Không thể xoá media', 500);
        }

        return $this->success(null, 'Media deleted successfully');
    }
}
