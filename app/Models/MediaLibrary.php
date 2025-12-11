<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Container model cho thư viện media chung.
 */
class MediaLibrary extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
    ];

    /**
     * Đăng ký collection cho thư viện.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('library')
            ->useDisk('public')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/avif',
                'image/svg+xml',
                'application/pdf',
            ]);
    }

    /**
     * Thumbnail dùng cho danh sách.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // disable conversions to keep upload/delete simple and avoid GD errors
    }
}
