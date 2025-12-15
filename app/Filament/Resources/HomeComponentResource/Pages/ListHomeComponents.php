<?php

namespace App\Filament\Resources\HomeComponentResource\Pages;

use App\Filament\Resources\HomeComponentResource;
use App\Services\NextJsRevalidationService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;

class ListHomeComponents extends ListRecords
{
    protected static string $resource = HomeComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Thêm Section mới'),
        ];
    }

    /**
     * Override để clear cache sau khi reorder
     * Filament reorderable không trigger model events
     */
    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        parent::reorderTable($order, $draggedRecordKey);

        // Clear tất cả cache liên quan đến home page
        Cache::forget('home-page-data');
        Cache::forget('home-components');

        // Trigger Next.js revalidation
        dispatch(function () {
            app(NextJsRevalidationService::class)->revalidateHome();
        })->afterResponse();
    }
}
