<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'parent_id',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Menu cha
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Các menu con
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /**
     * Scope: Chỉ lấy menu gốc (không có parent)
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Chỉ lấy menu active
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Sắp xếp theo order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    /**
     * Kiểm tra có phải menu cha không
     */
    public function isParent(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Kiểm tra có phải menu con không
     */
    public function isChild(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Lấy tất cả menu con (recursive) - cho tương lai nếu cần 3 cấp
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Lấy URL - trả về / nếu để trống
     */
    public function getResolvedUrlAttribute(): string
    {
        return $this->url ?: '/';
    }
}
