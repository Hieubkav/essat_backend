<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\User;
use App\Models\MediaLibrary;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Tổng người dùng', User::count())
                ->description('Tài khoản đã đăng ký')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 8]),

            Stat::make('Bài viết', Post::count())
                ->description(Post::where('active', true)->count() . ' đang hiển thị')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart([3, 5, 4, 6, 8, 7, 9, 10]),

            Stat::make('Media', MediaLibrary::count())
                ->description('Thư mục media')
                ->descriptionIcon('heroicon-m-photo')
                ->color('warning')
                ->chart([2, 4, 3, 5, 4, 6, 5, 7]),
        ];
    }
}
