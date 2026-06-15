<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function enable(Request $request, TenantAuthService $auth): JsonResponse
    {
        return response()->json($auth->enableTwoFactor($request->user()));
    }

    public function confirm(Request $request, TenantAuthService $auth): JsonResponse
    {
        $data = $request->validate(['code' => 'required|string']);
        $enabled = $auth->confirmTwoFactor($request->user(), $data['code']);

        return response()->json(['enabled' => $enabled], $enabled ? 200 : 422);
    }
}
