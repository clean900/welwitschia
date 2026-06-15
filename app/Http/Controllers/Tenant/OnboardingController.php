<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\OnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function proxypay(Request $request, OnboardingService $onboarding): JsonResponse
    {
        $data = $request->validate([
            'api_key' => 'required|string',
            'environment' => 'nullable|in:sandbox,production',
            'webhook_secret' => 'nullable|string',
        ]);

        $gateway = $onboarding->configureProxyPay(
            $data['api_key'],
            $data['environment'] ?? 'sandbox',
            $data['webhook_secret'] ?? null,
        );

        return response()->json(['active' => $gateway->active, 'environment' => $gateway->environment]);
    }

    public function sms(Request $request, OnboardingService $onboarding): JsonResponse
    {
        $data = $request->validate([
            'api_key' => 'required|string',
            'sender_id' => 'required|string|max:11',
        ]);

        $gateway = $onboarding->activateSms($data['api_key'], $data['sender_id'], $request->user()?->id);

        return response()->json(['active' => $gateway->active, 'sender_id' => $gateway->sender_id]);
    }

    public function status(OnboardingService $onboarding): JsonResponse
    {
        return response()->json($onboarding->status());
    }
}
