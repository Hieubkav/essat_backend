<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeComponent extends Model
{
    protected $fillable = [
        'type',
        'config',
        'order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'order' => 'int',
            'active' => 'bool',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
