<?php

namespace App\Modules\__MODEL__\Services;

use App\Modules\Auth\Models\User;
use App\Services\BaseCachedService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class __MODEL__Service extends BaseCachedService
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
        $token = $user->createToken('api')->plainTextToken;
        return ['token' => $token, 'user' => $user];
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



}
