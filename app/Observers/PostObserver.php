<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\ContentImageService;

class PostObserver
{
    public function __construct(private ContentImageService $contentImages)
    {
    }

    public function updating(Post $post): void
    {
        $oldContent = $post->getOriginal('content');
        $newContent = $post->getAttribute('content');

        if (is_string($oldContent) && is_string($newContent)) {
            $this->contentImages->deleteRemovedImages($oldContent, $newContent, ['posts']);
        }
    }

    public function deleting(Post $post): void
    {
        $content = $post->getAttribute('content');

        if (is_string($content)) {
            $this->contentImages->deleteAllImages($content, ['posts']);
        }
    }
}
