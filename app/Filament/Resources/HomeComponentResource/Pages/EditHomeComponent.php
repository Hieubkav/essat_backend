<?php

namespace App\Filament\Resources\HomeComponentResource\Pages;

use App\Filament\Resources\HomeComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomeComponent extends EditRecord
{
    protected static string $resource = HomeComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
