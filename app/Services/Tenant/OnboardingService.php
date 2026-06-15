<?php

namespace App\Services\Tenant;

use App\Models\AuditLog;
use App\Models\PaymentGateway;
use App\Models\SmsGateway;
use App\Support\TenantSecrets;

/**
 * Wizard de onboarding (passos 2 e 3). Executa SEMPRE em contexto de tenant.
 * - ProxyPay: configurado pelo CLIENTE (a sua própria API Key).
 * - TelcoSMS: activado pelo ADMIN da plataforma (cliente não vê a chave).
 */
class OnboardingService
{
    public function configureProxyPay(string $apiKey, string $environment = 'sandbox', ?string $webhookSecret = null): PaymentGateway
    {
        $gateway = PaymentGateway::firstOrNew([
            'provider' => 'proxypay',
            'environment' => $environment,
        ]);

        $gateway->setApiKey($apiKey);
        if ($webhookSecret) {
            $gateway->setWebhookSecret($webhookSecret);
        }
        $gateway->active = true;
        $gateway->activated_at = now();
        $gateway->save();

        AuditLog::record('onboarding.proxypay_configured', [
            'environment' => $environment,
            'api_key' => TenantSecrets::mask($apiKey), // nunca a chave em claro
        ]);

        return $gateway;
    }

    public function activateSms(string $apiKey, string $senderId, ?int $adminId = null): SmsGateway
    {
        $gateway = SmsGateway::firstOrNew(['provider' => 'telcosms']);

        $gateway->setApiKey($apiKey);
        $gateway->sender_id = $senderId;
        $gateway->active = true;
        $gateway->activated_by_admin = $adminId;
        $gateway->activated_at = now();
        $gateway->save();

        AuditLog::record('onboarding.sms_activated', [
            'sender_id' => $senderId,
            'api_key' => TenantSecrets::mask($apiKey),
            'activated_by_admin' => $adminId,
        ]);

        return $gateway;
    }

    /** Estado do wizard para o painel do cliente. */
    public function status(): array
    {
        return [
            'proxypay' => PaymentGateway::where('provider', 'proxypay')->where('active', true)->exists(),
            'sms' => SmsGateway::where('provider', 'telcosms')->where('active', true)->exists(),
        ];
    }
}
