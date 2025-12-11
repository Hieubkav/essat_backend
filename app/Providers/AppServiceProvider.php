<?php

namespace App\Providers;

use App\Models\RichEditorMedia;
use App\Observers\RichEditorMediaObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Observers\MediaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Media::observe(MediaObserver::class);
        RichEditorMedia::observe(RichEditorMediaObserver::class);
    }
}
