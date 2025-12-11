<?php

namespace App\Repositories;

use App\Models\Post;

/**
 * Post Repository
 */
class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct()
    {
        $this->model = new Post();
    }
}
