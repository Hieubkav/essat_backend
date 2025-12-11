<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends ApiController
{
    /**
     * Get all users (Admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $users = User::paginate($perPage);

        return $this->success(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * Get single user (Admin only)
     */
    public function show(User $user): JsonResponse
    {
        return $this->success(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Create user (Admin only)
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return $this->created(
            new UserResource($user),
            'User created successfully'
        );
    }

    /**
     * Update user (Admin only)
     */
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['role'])) {
            if ($validated['role'] === 'user' && $user->isAdmin()) {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    throw new ApiException('Cannot demote the last admin user', 400);
                }
            }

            $user->role = $validated['role'];
        }

        $user->save();

        return $this->success(
            new UserResource($user),
            'User updated successfully'
        );
    }

    /**
     * Delete user (Admin only)
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                throw new ApiException('Cannot delete the last admin user', 400);
            }
        }

        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }
}
