<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use GuzzleHttp\Client;
use RuntimeException;

/**
 * ProxyPay por tenant. Cada empresa cliente usa a SUA API Key (encriptada).
 * Os fundos vão directamente para a conta bancária do cliente — a Welwitschia
 * nunca lhes toca.
 */
class ProxyPayService
{
    public function __construct(protected ?Client $http = null)
    {
    }

    protected function gateway(): PaymentGateway
    {
        $gw = PaymentGateway::where('provider', 'proxypay')->where('active', true)->first();
        if (! $gw) {
            throw new RuntimeException('Gateway ProxyPay não configurado/activo para este tenant.');
        }

        return $gw;
    }

    protected function client(PaymentGateway $gw): Client
    {
        $base = $gw->environment === 'production'
            ? 'https://api.proxypay.co.ao'
            : config('services.proxypay.base_url', env('PROXYPAY_BASE_URL', 'https://api.sandbox.proxypay.co.ao'));

        return $this->http ?? new Client([
            'base_uri' => rtrim($base, '/') . '/',
            'headers' => [
                'Authorization' => 'Token ' . $gw->getApiKey(),
                'Accept' => 'application/vnd.proxypay.v2+json',
            ],
            'timeout' => 20,
        ]);
    }

    /**
     * Gera/regista uma referência de pagamento na ProxyPay.
     * Devolve a referência criada.
     */
    public function createReference(float $amount, array $customFields = [], ?string $reference = null): string
    {
        $gw = $this->gateway();
        $reference = $reference ?: $this->generateReferenceId();

        $this->client($gw)->put("references/{$reference}", [
            'json' => [
                'amount' => number_format($amount, 2, '.', ''),
                'custom_fields' => $customFields,
            ],
        ]);

        return $reference;
    }

    /** Gera um id de referência numérico (placeholder — ProxyPay aceita refs próprias). */
    protected function generateReferenceId(): string
    {
        return (string) random_int(100000000, 999999999);
    }
}
