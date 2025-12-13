<?php

namespace App\Filament\Resources\HomeComponentResource\Pages;

use App\Filament\Resources\HomeComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
}
