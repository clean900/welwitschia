<?php

namespace App\Models;

use App\Support\TenantSecrets;
use Illuminate\Database\Eloquent\Model;

/**
 * Gateway TelcoSMS por tenant. API Key activada pelo ADMIN, NUNCA exposta ao cliente.
 */
class SmsGateway extends Model
{
    protected $fillable = [
        'provider', 'api_key_enc', 'sender_id',
        'price_per_sms', 'active',
        'activated_by_admin', 'activated_at',
    ];

    protected $hidden = ['api_key_enc'];

    protected $casts = [
        'active' => 'boolean',
        'activated_at' => 'datetime',
        'price_per_sms' => 'decimal:2',
    ];

    public function setApiKey(?string $plain): void
    {
        $this->api_key_enc = TenantSecrets::encrypt($plain);
    }

    public function getApiKey(): ?string
    {
        return TenantSecrets::decrypt($this->api_key_enc);
    }
}
