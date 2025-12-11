<?php

namespace App\Services;

use App\Models\Setting;
use App\Repositories\SettingRepository;

class SettingService extends BaseService
{
    public function __construct(private SettingRepository $settings)
    {
    }

    /**
     * Lấy bản ghi setting duy nhất.
     */
    public function get(): Setting
    {
        return $this->settings->getSingleton();
    }

    /**
     * Cập nhật bản ghi setting duy nhất.
     */
    public function update(array $data): Setting
    {
        $setting = $this->settings->getSingleton();
        $this->settings->update($setting->id, $data);

        return $setting->refresh();
    }
}
