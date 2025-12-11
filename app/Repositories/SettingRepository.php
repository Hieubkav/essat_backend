<?php

namespace App\Repositories;

use App\Models\Setting;

/**
 * Setting Repository
 */
class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct()
    {
        $this->model = new Setting();
    }

    /**
     * Đảm bảo chỉ một bản ghi setting tồn tại và trả về nó.
     */
    public function getSingleton(): Setting
    {
        return $this->model->firstOrCreate(
            ['singleton' => Setting::SINGLETON_KEY],
            [
                'site_name' => 'My Website',
                'primary_color' => '#000000',
                'secondary_color' => '#FFFFFF',
                'seo_title' => 'My Website',
                'singleton' => Setting::SINGLETON_KEY,
            ]
        );
    }
}
