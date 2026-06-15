<?php

namespace App\Models;

use App\Support\TenantSecrets;
use Illuminate\Database\Eloquent\Model;

/**
 * Gateway ProxyPay por tenant. API Keys encriptadas, nunca expostas.
 */
class PaymentGateway extends Model
{
    protected $fillable = [
        'provider', 'environment', 'api_key_enc',
        'api_secret_enc', 'webhook_secret_enc',
        'merchant_id', 'config', 'active',
        'activated_by', 'activated_at',
    ];

    protected $hidden = ['api_key_enc', 'api_secret_enc', 'webhook_secret_enc'];

    protected $casts = [
        'config' => 'array',
        'active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    // --- Segredos: set encripta, get desencripta (nunca persistir em claro) ---

    public function setApiKey(?string $plain): void
    {
        $this->api_key_enc = TenantSecrets::encrypt($plain);
    }

    public function getApiKey(): ?string
    {
        return TenantSecrets::decrypt($this->api_key_enc);
    }

    public function setApiSecret(?string $plain): void
    {
        $this->api_secret_enc = TenantSecrets::encrypt($plain);
    }

    public function getApiSecret(): ?string
    {
        return TenantSecrets::decrypt($this->api_secret_enc);
    }

    public function setWebhookSecret(?string $plain): void
    {
        $this->webhook_secret_enc = TenantSecrets::encrypt($plain);
    }

    public function getWebhookSecret(): ?string
    {
        return TenantSecrets::decrypt($this->webhook_secret_enc);
    }
}
