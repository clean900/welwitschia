<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envia um evento de domínio para o n8n (webhook). Não bloqueia o pedido —
 * corre em fila e nunca rebenta o fluxo principal.
 */
class SendN8nWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public string $event,
        public array $payload,
        public ?string $tenant = null,
    ) {
    }

    public function handle(): void
    {
        $base = config('services.n8n.url');
        if (! $base) {
            return;
        }

        $url = rtrim($base, '/') . '/' . ltrim($this->event, '/');

        try {
            Http::timeout(8)->acceptJson()->post($url, [
                'event' => $this->event,
                'tenant' => $this->tenant,
                'data' => $this->payload,
            ]);
        } catch (\Throwable $e) {
            Log::warning('n8n webhook falhou', ['event' => $this->event, 'error' => $e->getMessage()]);
        }
    }
}
