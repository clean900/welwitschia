<?php

namespace App\Services\Sms;

use App\Models\AuditLog;
use App\Models\SmsGateway;
use GuzzleHttp\Client;
use RuntimeException;

/**
 * TelcoSMS por tenant. API Key activada pelo ADMIN (cliente não a vê).
 * SMS saem com o Sender ID da empresa do cliente. Exposto como "Serviço SMS ONDAKA"
 * — nunca mostrar "TelcoSMS" ao utilizador final.
 */
class TelcoSmsService
{
    public function __construct(protected ?Client $http = null)
    {
    }

    protected function gateway(): SmsGateway
    {
        $gw = SmsGateway::where('provider', 'telcosms')->where('active', true)->first();
        if (! $gw) {
            throw new RuntimeException('Gateway SMS não activado para este tenant.');
        }

        return $gw;
    }

    /**
     * Envia um SMS. Devolve true em caso de aceitação pelo gateway.
     */
    public function send(string $to, string $message): bool
    {
        $gw = $this->gateway();

        $client = $this->http ?? new Client([
            'base_uri' => rtrim(env('TELCOSMS_BASE_URL', 'https://api.telcosms.co.ao'), '/') . '/',
            'timeout' => 20,
        ]);

        $client->post('messages', [
            'headers' => ['Authorization' => 'Bearer ' . $gw->getApiKey()],
            'json' => [
                'from' => $gw->sender_id,
                'to' => $to,
                'text' => $message,
            ],
        ]);

        // Auditar SEM expor a API Key.
        AuditLog::record('sms.sent', [
            'to' => $to,
            'sender_id' => $gw->sender_id,
            'length' => mb_strlen($message),
        ]);

        return true;
    }
}
