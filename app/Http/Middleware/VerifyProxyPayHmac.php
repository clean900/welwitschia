<?php

namespace App\Http\Middleware;

use App\Models\PaymentGateway;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Valida o HMAC do callback ProxyPay contra o webhook secret do tenant.
 * Se não houver secret configurado, deixa passar (sandbox/dev).
 */
class VerifyProxyPayHmac
{
    public function handle(Request $request, Closure $next): Response
    {
        $gateway = PaymentGateway::where('provider', 'proxypay')->where('active', true)->first();
        $secret = $gateway?->getWebhookSecret();

        if ($secret) {
            $signature = $request->header('X-Signature') ?? $request->header('Signature', '');
            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            if (! $signature || ! hash_equals($expected, $signature)) {
                abort(401, 'Assinatura HMAC inválida.');
            }
        }

        return $next($request);
    }
}
