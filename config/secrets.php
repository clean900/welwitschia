<?php

return [
    /**
     * Chave dedicada para encriptar segredos dos tenants (API Keys ProxyPay/TelcoSMS),
     * separada do APP_KEY. Formato: base64:<32 bytes>.
     */
    'tenant_key' => env('TENANT_SECRETS_KEY'),

    'cipher' => 'aes-256-gcm',
];
