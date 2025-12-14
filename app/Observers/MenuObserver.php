<?php

namespace App\Observers;

use App\Models\Menu;
use App\Services\NextJsRevalidationService;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    public function __construct(
        protected NextJsRevalidationService $revalidationService
    ) {}

    public function saved(Menu $menu): void
    {
        $this->clearCache();
        $this->triggerRevalidation();
    }

    public function deleted(Menu $menu): void
    {
        $this->clearCache();
        $this->triggerRevalidation();
    }

    protected function clearCache(): void
    {
        Cache::forget('navigation-menus');
        Cache::forget('home-page-data');
    }

    protected function triggerRevalidation(): void
    {
        dispatch(function () {
            app(NextJsRevalidationService::class)->revalidateHome();
        })->afterResponse();
    }
}
