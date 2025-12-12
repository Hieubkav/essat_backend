<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Illuminate\Support\Str;

class EditCategory extends BaseEditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['name']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Xóa'),
        ];
    }
}
