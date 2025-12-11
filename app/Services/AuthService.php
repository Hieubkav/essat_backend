<?php

namespace App\Services;

use App\Exceptions\AuthenticationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService extends BaseService
{
    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $user;
    }

    /**
     * Login user and generate token
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new AuthenticationException(
                'Invalid credentials',
                401,
                null,
                ['email' => ['The provided credentials are invalid.']]
            );
        }

        // Revoke existing tokens
        $user->tokens()->delete();

        // Generate new token
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(User $user): bool
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            return (bool) $token->delete();
        }

        return false;
    }

    /**
     * Get authenticated user
     */
    public function getAuthUser(User $user): User
    {
        return $user;
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        $user->save();

        return $user;
    }

    /**
     * Change password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new AuthenticationException(
                'Password change failed',
                400,
                null,
                ['current_password' => ['The provided password is incorrect.']]
            );
        }

        $user->password = Hash::make($newPassword);

        if (!$user->save()) {
            return false;
        }

        // Thu hồi toàn bộ personal access tokens sau khi đổi mật khẩu
        $user->tokens()->delete();

        return true;
    }
}
