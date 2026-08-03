<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\User;
use App\Services\BaseCachedService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class AuthService extends BaseCachedService
{

    public function __construct()
    {
        parent::__construct(new User());
    }

    public function login(array $data): array
    {
        if (!Auth::attempt($data)) {
            abort(401, 'Invalid credentials');
        }

        $user = Auth::user();

        // Load roles + permissions
        $user->load('roles.permissions', 'permissions');

        // Collect permissions (role + direct)

        $token = $user->createToken('api')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'roles' => $user->roles->pluck('slug'),
            'permissions' => $user->getAllPermissions(),

        ];
    }

    public function refreshCache(): void
    {
        // 🔥 Flush ALL Redis cache
        Cache::flush();
    }

    public function logout(): void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }
        // Delete current token
        $user->currentAccessToken()?->delete();
        // 🔥 Flush ALL Redis cache
        Cache::flush();
    }

    public function getUser()
    {
        return $this->remember(
            $this->byIdCacheKey(auth()->id()),
            fn() => Auth::user()
        );
    }

    public function changePassword(array $data): void
    {
        $user = auth()->user();

        if (!password_verify($data['current_password'], $user->password)) {
            abort(400, 'Current password is incorrect');
        }

        $user->password = bcrypt($data['new_password']);
        $user->save();
    }
}
