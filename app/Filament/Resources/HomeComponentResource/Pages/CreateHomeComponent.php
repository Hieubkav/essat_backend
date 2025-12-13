<?php

namespace App\Filament\Resources\HomeComponentResource\Pages;

use App\Filament\Resources\HomeComponentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomeComponent extends CreateRecord
{
    protected static string $resource = HomeComponentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
