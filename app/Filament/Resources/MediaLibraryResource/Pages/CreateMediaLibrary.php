<?php

namespace App\Filament\Resources\MediaLibraryResource\Pages;

use App\Filament\Resources\MediaLibraryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaLibrary extends CreateRecord
{
    protected static string $resource = MediaLibraryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
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
