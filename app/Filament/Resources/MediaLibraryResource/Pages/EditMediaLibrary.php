<?php

namespace App\Filament\Resources\MediaLibraryResource\Pages;

use App\Filament\Pages\BaseEditRecord;
use App\Filament\Resources\MediaLibraryResource;
use Filament\Actions;

class EditMediaLibrary extends BaseEditRecord
{
    protected static string $resource = MediaLibraryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Xóa'),
        ];
    }

    protected function afterSave(): void
    {
        if (empty($this->record->name)) {
            $firstMedia = $this->record->getFirstMedia('library');
            if ($firstMedia) {
                $this->record->update([
                    'name' => pathinfo($firstMedia->file_name, PATHINFO_FILENAME),
                ]);
            }
        }
    }
}
