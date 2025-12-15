<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\HomeComponent;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeComponentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HomeComponent');
    }

    public function view(AuthUser $authUser, HomeComponent $homeComponent): bool
    {
        return $authUser->can('View:HomeComponent');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HomeComponent');
    }

    public function update(AuthUser $authUser, HomeComponent $homeComponent): bool
    {
        return $authUser->can('Update:HomeComponent');
    }

    public function delete(AuthUser $authUser, HomeComponent $homeComponent): bool
    {
        return $authUser->can('Delete:HomeComponent');
    }

}