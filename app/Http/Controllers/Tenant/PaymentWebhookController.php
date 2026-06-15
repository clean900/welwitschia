<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProxyPayCallback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recebe callbacks ProxyPay. NÃO bloqueia: despacha para a fila 'payments'
 * (afterCommit) e responde 202. Idempotência garantida no job.
 */
class PaymentWebhookController extends Controller
{
    public function proxypay(Request $request): JsonResponse
    {
        $payload = $request->all();

        ProcessProxyPayCallback::dispatch($payload)->afterCommit();

        return response()->json(['status' => 'accepted'], 202);
    }
}
