<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Setting\SettingStoreRequest;
use App\Http\Requests\Setting\SettingUpdateRequest;
use App\Http\Resources\SettingResource;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;

class SettingController extends ApiController
{
    public function __construct(private SettingService $settingService)
    {
    }

    /**
     * Lấy thông tin setting (singleton).
     */
    public function show(): JsonResponse
    {
        $setting = $this->settingService->get();

        return $this->success(
            new SettingResource($setting),
            'Setting retrieved successfully'
        );
    }

    /**
     * Khởi tạo/cập nhật setting khi chưa có.
     */
    public function store(SettingStoreRequest $request): JsonResponse
    {
        $setting = $this->settingService->update($request->validated());

        return $this->created(
            new SettingResource($setting),
            'Setting saved successfully'
        );
    }

    /**
     * Cập nhật setting.
     */
    public function update(SettingUpdateRequest $request): JsonResponse
    {
        $setting = $this->settingService->update($request->validated());

        return $this->success(
            new SettingResource($setting),
            'Setting updated successfully'
        );
    }
}
