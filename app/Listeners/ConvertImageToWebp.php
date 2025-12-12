<?php

namespace App\Listeners;

use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class ConvertImageToWebp
{
    protected array $convertibleMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if (! in_array($media->mime_type, $this->convertibleMimeTypes)) {
            return;
        }

        $originalPath = $media->getPath();
        $directory = dirname($originalPath);
        $newFileName = pathinfo($media->file_name, PATHINFO_FILENAME) . '.webp';
        $webpPath = $directory . DIRECTORY_SEPARATOR . $newFileName;

        Image::useImageDriver(ImageDriver::Gd)
            ->load($originalPath)
            ->quality(80)
            ->save($webpPath);

        if (file_exists($originalPath) && $originalPath !== $webpPath) {
            unlink($originalPath);
        }

        $media->file_name = $newFileName;
        $media->mime_type = 'image/webp';
        $media->size = filesize($webpPath);
        $media->saveQuietly();
    }
}
