<?php

namespace App\Observers;

use App\Models\RichEditorMedia;
use Illuminate\Support\Facades\Storage;

class RichEditorMediaObserver
{
    public function deleted(RichEditorMedia $media): void
    {
        if ($media->file_path && Storage::disk($media->disk)->exists($media->file_path)) {
            Storage::disk($media->disk)->delete($media->file_path);
        }
    }
}
