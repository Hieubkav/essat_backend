<?php

namespace App\Models;

use App\Casts\LexicalToHtmlCast;
use App\Models\Concerns\HasRichEditorMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use HasRichEditorMedia;
    use InteractsWithMedia;

    protected array $richEditorFields = ['content'];

    protected string $richEditorContentDirectory = 'products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'content',
        'thumbnail',
        'price',
        'active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'order' => 'integer',
            'price' => 'decimal:0',
            'content' => LexicalToHtmlCast::class,
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150);

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600);
    }
}
