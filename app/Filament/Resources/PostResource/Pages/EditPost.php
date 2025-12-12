<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\PostResource;
use Filament\Actions;

class EditPost extends BaseEditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Xóa'),
        ];
    }
}
