<?php

namespace App\Services\Agt;

use RuntimeException;

/**
 * Assinatura JWS compacta com RS256 (RSA + SHA-256) — exigido pela AGT (FE).
 * // VALIDAR a canonização do payload contra a homologação da AGT.
 */
class JwsSigner
{
    public function sign(array $payload, string $privateKeyPem): string
    {
        $key = openssl_pkey_get_private($privateKeyPem);
        if ($key === false) {
            throw new RuntimeException('Chave privada AGT inválida.');
        }

        $header = $this->b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $body = $this->b64(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $signingInput = $header . '.' . $body;

        openssl_sign($signingInput, $signature, $key, OPENSSL_ALGO_SHA256);

        return $signingInput . '.' . $this->b64($signature);
    }

    public function verify(string $jws, string $publicKeyPem): bool
    {
        $parts = explode('.', $jws);
        if (count($parts) !== 3) {
            return false;
        }
        [$header, $body, $sig] = $parts;

        $key = openssl_pkey_get_public($publicKeyPem);
        if ($key === false) {
            return false;
        }

        return openssl_verify("{$header}.{$body}", $this->unb64($sig), $key, OPENSSL_ALGO_SHA256) === 1;
    }

    private function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function unb64(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
