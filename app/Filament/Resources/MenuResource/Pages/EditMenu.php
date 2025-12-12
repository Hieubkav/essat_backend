<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\MenuResource;
use Filament\Actions;

class EditMenu extends BaseEditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Xóa'),
        ];
    }
}
