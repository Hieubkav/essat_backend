<?php

namespace App\Providers;

use App\Listeners\ConvertImageToWebp;
use App\Models\HomeComponent;
use App\Models\Menu;
use App\Models\RichEditorMedia;
use App\Observers\HomeComponentObserver;
use App\Observers\MenuObserver;
use App\Observers\RichEditorMediaObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
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
        HomeComponent::observe(HomeComponentObserver::class);
        Menu::observe(MenuObserver::class);

        Event::listen(MediaHasBeenAddedEvent::class, ConvertImageToWebp::class);
    }
}
