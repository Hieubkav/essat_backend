<?php

namespace Database\Seeders;

use App\Models\MediaLibrary;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        // Skip - để tránh tạo duplicate media mỗi lần chạy seeder
        // Tạo media thủ công bằng UI hoặc bằng tinker
        return;
    }
}
