<?php

namespace App\Services\Automation;

use App\Jobs\SendN8nWebhook;

/**
 * Ponto único de emissão de eventos para o n8n. No-op se N8N_WEBHOOK_URL não
 * estiver configurado — por isso é seguro chamar em qualquer lado.
 */
class WebhookDispatcher
{
    public static function send(string $event, array $payload): void
    {
        if (! config('services.n8n.url')) {
            return;
        }

        SendN8nWebhook::dispatch(
            $event,
            $payload,
            function_exists('tenant') && tenancy()->initialized ? tenant('id') : null,
        );
    }
}
