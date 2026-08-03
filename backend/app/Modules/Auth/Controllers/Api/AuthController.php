<?php

namespace App\Modules\Auth\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\AuthRequest;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Resources\AuthResource;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}


    public function login(AuthRequest $request)
    {
        $loginData = $this->authService->login($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $loginData['token'],
            'user' => $loginData['user'],
            'roles' => $loginData['roles'],
            'permissions' => $loginData['permissions'],
        ]);
    }

    public function refresh()
    {
        $token = $this->authService->refreshCache();
        return response()->json([
            'success' => true,
            'message' => 'Cache refreshed successfully',
        ]);
    }
    public function logout()
    {
        $this->authService->logout();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }

    public function user()
    {
        return new AuthResource($this->authService->getUser());
    }


    public function passwordChange(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ]);

        $this->authService->changePassword($request->only('current_password', 'new_password'));

        return response()->json(['success' => true, 'message' => 'Password changed successfully']);
    }
}
