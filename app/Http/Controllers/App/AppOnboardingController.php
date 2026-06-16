<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\SmsGateway;
use App\Services\Tenant\OnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Configuração de integrações da empresa (ProxyPay + SMS) — /app/onboarding.
 */
class AppOnboardingController extends Controller
{
    public function index(OnboardingService $onboarding): Response
    {
        $proxypay = PaymentGateway::where('provider', 'proxypay')->first();
        $sms = SmsGateway::where('provider', 'telcosms')->first();

        return Inertia::render('App/Onboarding/Index', [
            'status' => $onboarding->status(),
            'proxypay' => $proxypay ? [
                'environment' => $proxypay->environment,
                'active' => $proxypay->active,
            ] : null,
            'sms' => $sms ? [
                'sender_id' => $sms->sender_id,
                'active' => $sms->active,
            ] : null,
        ]);
    }

    public function saveProxyPay(Request $request, OnboardingService $onboarding): RedirectResponse
    {
        $data = $request->validate([
            'api_key' => 'required|string',
            'environment' => 'nullable|in:sandbox,production',
            'webhook_secret' => 'nullable|string',
        ]);

        $onboarding->configureProxyPay($data['api_key'], $data['environment'] ?? 'sandbox', $data['webhook_secret'] ?? null);

        return back()->with('success', 'ProxyPay configurado com sucesso.');
    }

    public function saveSms(Request $request, OnboardingService $onboarding): RedirectResponse
    {
        $data = $request->validate([
            'api_key' => 'required|string',
            'sender_id' => 'required|string|max:11',
        ]);

        $onboarding->activateSms($data['api_key'], $data['sender_id'], Auth::id());

        return back()->with('success', 'Serviço de SMS activado.');
    }
}
