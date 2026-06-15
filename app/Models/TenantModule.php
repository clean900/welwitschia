<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    protected $fillable = [
        'tenant_id', 'module', 'active',
        'monthly_price', 'activated_at', 'expires_at', 'config',
    ];

    protected $casts = [
        'active' => 'boolean',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'config' => 'array',
        'monthly_price' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
