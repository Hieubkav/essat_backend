<?php

namespace App\Repositories;

use App\Models\Setting;

/**
 * Setting Repository Interface
 */
interface SettingRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Lấy (hoặc tạo) bản ghi setting duy nhất.
     */
    public function getSingleton(): Setting;
}
