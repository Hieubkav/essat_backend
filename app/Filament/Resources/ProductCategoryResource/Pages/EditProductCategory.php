<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\ProductCategoryResource;
use Filament\Actions;

class EditProductCategory extends BaseEditRecord
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
