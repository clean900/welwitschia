<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request, TenantAuthService $auth): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'code' => 'nullable|string',
        ]);

        $result = $auth->login($data['email'], $data['password'], $data['code'] ?? null);

        return response()->json([
            'token' => $result['token'],
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'roles' => $result['user']->getRoleNames(),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->only(['id', 'name', 'email']));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ok' => true]);
    }
}
