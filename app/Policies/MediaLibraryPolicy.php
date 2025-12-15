<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MediaLibrary;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaLibraryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MediaLibrary');
    }

    public function view(AuthUser $authUser, MediaLibrary $mediaLibrary): bool
    {
        return $authUser->can('View:MediaLibrary');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MediaLibrary');
    }

    public function update(AuthUser $authUser, MediaLibrary $mediaLibrary): bool
    {
        return $authUser->can('Update:MediaLibrary');
    }

    public function delete(AuthUser $authUser, MediaLibrary $mediaLibrary): bool
    {
        return $authUser->can('Delete:MediaLibrary');
    }

}