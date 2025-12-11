<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return $this->created(
            new UserResource($user),
            'User registered successfully'
        );
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->email,
            $request->password
        );

        return $this->success(
            [
                'user' => new UserResource($result['user']),
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
            ],
            'Login successful'
        );
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()),
            'User profile retrieved'
        );
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile($request->user(), $request->validated());

        return $this->success(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            $request->user(),
            $request->current_password,
            $request->new_password
        );

        return $this->success(null, 'Password changed successfully');
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully');
    }
}
