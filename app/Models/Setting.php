<?php

namespace App\Models;

use App\Observers\SettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(SettingObserver::class)]
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
        'logo',
        'favicon',
        'placeholder',
    ];
}
