<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Support\Enums\Width;

class EditUser extends BaseEditRecord
{
    protected static string $resource = UserResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Xóa'),
        ];
    }
}
