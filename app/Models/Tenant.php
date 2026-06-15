<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

/**
 * Tenant = empresa cliente. Schema PostgreSQL isolado (tenant_<id>).
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Colunas reais da tabela tenants (não vão para o JSON `data`).
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'nif',
            'plan_id',
            'status',
            'trial_ends_at',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function modules()
    {
        return $this->hasMany(TenantModule::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasModuleActive(string $module): bool
    {
        return $this->modules()
            ->where('module', $module)
            ->where('active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}
