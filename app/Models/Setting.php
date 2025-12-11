<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public const SINGLETON_KEY = 'default';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'site_name',
        'primary_color',
        'secondary_color',
        'seo_title',
        'seo_description',
        'phone',
        'address',
        'email',
        'singleton',
    ];
}
