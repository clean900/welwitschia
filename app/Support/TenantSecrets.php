<?php

namespace App\Support;

use Illuminate\Encryption\Encrypter;
use RuntimeException;

/**
 * Encriptação de segredos por tenant (API Keys ProxyPay/TelcoSMS) com chave
 * dedicada (TENANT_SECRETS_KEY), isolada do APP_KEY. AES-256-GCM.
 */
class TenantSecrets
{
    protected static ?Encrypter $encrypter = null;

    protected static function encrypter(): Encrypter
    {
        if (static::$encrypter instanceof Encrypter) {
            return static::$encrypter;
        }

        $key = config('secrets.tenant_key');
        if (! $key) {
            throw new RuntimeException('TENANT_SECRETS_KEY não está definida.');
        }
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return static::$encrypter = new Encrypter($key, config('secrets.cipher', 'aes-256-gcm'));
    }

    public static function encrypt(?string $plain): ?string
    {
        if ($plain === null || $plain === '') {
            return null;
        }

        return static::encrypter()->encryptString($plain);
    }

    public static function decrypt(?string $cipher): ?string
    {
        if ($cipher === null || $cipher === '') {
            return null;
        }

        return static::encrypter()->decryptString($cipher);
    }

    /** Mascara uma chave para logs: 'abcd****'. */
    public static function mask(?string $plain): string
    {
        if (! $plain) {
            return '****';
        }

        return substr($plain, 0, 4) . '****';
    }
}
