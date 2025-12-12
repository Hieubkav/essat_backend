<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\ProductResource;
use Filament\Actions;

class EditProduct extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
