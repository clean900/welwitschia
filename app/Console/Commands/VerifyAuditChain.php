<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Console\Command;

/**
 * Verifica a integridade da cadeia de auditoria (hash-chain) de um tenant.
 */
class VerifyAuditChain extends Command
{
    protected $signature = 'audit:verify-chain {--tenant= : ID do tenant a verificar (todos se omitido)}';

    protected $description = 'Verifica a integridade da cadeia de auditoria (hash-chain) por tenant';

    public function handle(): int
    {
        $tenants = $this->option('tenant')
            ? Tenant::where('id', $this->option('tenant'))->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant encontrado.');

            return self::SUCCESS;
        }

        $allOk = true;

        foreach ($tenants as $tenant) {
            $ok = $tenant->run(fn () => AuditLog::verifyChain());
            $count = $tenant->run(fn () => AuditLog::count());

            if ($ok) {
                $this->info("✔ {$tenant->id}: cadeia íntegra ({$count} registos).");
            } else {
                $allOk = false;
                $this->error("✘ {$tenant->id}: CADEIA QUEBRADA — possível adulteração.");
            }
        }

        return $allOk ? self::SUCCESS : self::FAILURE;
    }
}
