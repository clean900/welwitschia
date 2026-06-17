<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgtSetting;
use App\Models\PaymentGateway;
use App\Models\SmsGateway;
use App\Models\Tenant;
use App\Services\Tenant\OnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Detalhe de uma empresa no back-office + activação de TelcoSMS pelo admin
 * (o cliente nunca vê a API Key — governança da plataforma).
 */
class AdminCompanyController extends Controller
{
    public function show(Tenant $tenant): Response
    {
        $status = $tenant->run(fn () => [
            'sms' => SmsGateway::where('provider', 'telcosms')->first(),
            'proxypay' => PaymentGateway::where('provider', 'proxypay')->first(),
            'agt' => AgtSetting::where('active', true)->first(),
        ]);

        return Inertia::render('Admin/Company', [
            'company' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'status' => $tenant->status,
                'nif' => $tenant->nif,
            ],
            'sms' => $status['sms'] ? [
                'sender_id' => $status['sms']->sender_id,
                'active' => $status['sms']->active,
            ] : null,
            'proxypay' => $status['proxypay'] ? [
                'active' => $status['proxypay']->active,
                'environment' => $status['proxypay']->environment,
            ] : null,
            'agt' => $status['agt'] ? [
                'tax_registration_number' => $status['agt']->tax_registration_number,
                'establishment_number' => $status['agt']->establishment_number,
                'active' => $status['agt']->active,
            ] : null,
        ]);
    }

    public function saveAgt(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'tax_registration_number' => 'required|string|max:20',
            'establishment_number' => 'nullable|string|max:10',
            'private_key' => 'required|string',
        ]);

        $tenant->run(function () use ($data) {
            $setting = AgtSetting::firstOrNew([]);
            $setting->tax_registration_number = $data['tax_registration_number'];
            $setting->establishment_number = $data['establishment_number'] ?: '001';
            $setting->private_key = $data['private_key'];
            $setting->active = true;
            $setting->save();
        });

        return back()->with('success', "Emissor AGT configurado para {$tenant->name}.");
    }

    public function activateSms(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'api_key' => 'required|string',
            'sender_id' => 'required|string|max:11',
        ]);

        $adminId = Auth::guard('admin')->id();

        $tenant->run(fn () => (new OnboardingService())->activateSms($data['api_key'], $data['sender_id'], $adminId));

        return back()->with('success', "Serviço de SMS activado para {$tenant->name}.");
    }
}
