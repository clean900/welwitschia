<?php

namespace App\Services\Tenant;

use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\Tenant\PgcAngolaSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Provisiona uma nova empresa (tenant): schema isolado, roles, admin e subscrição.
 * Passo 1 do wizard de onboarding (dados da empresa).
 */
class TenantProvisioningService
{
    /**
     * @param  array{company_name:string, slug:string, nif?:string, plan:string, admin_name:string, admin_email:string, admin_password:string}  $data
     */
    public function register(array $data): Tenant
    {
        if (Tenant::where('slug', $data['slug'])->orWhere('id', $data['slug'])->exists()) {
            throw ValidationException::withMessages(['slug' => 'Já existe uma empresa com este identificador.']);
        }

        $plan = Plan::where('slug', $data['plan'])->firstOrFail();

        // Central: cria o tenant (dispara criação + migração do schema).
        $tenant = Tenant::create([
            'id' => $data['slug'],
            'name' => $data['company_name'],
            'slug' => $data['slug'],
            'nif' => $data['nif'] ?? null,
            'plan_id' => $plan->id,
            'status' => $plan->trial_days > 0 ? 'trial' : 'active',
            'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
        ]);

        // Subdomínio do tenant (ex.: acme → acme.welwitschia.ao). Resolvido por subdomínio.
        $tenant->domains()->create(['domain' => $data['slug']]);

        // Central: subscrição inicial.
        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_period' => 'monthly',
            'amount' => $plan->price_monthly,
            'currency' => 'AOA',
            'status' => $plan->trial_days > 0 ? 'trial' : 'pending',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Tenant: roles + admin + auditoria, dentro do schema isolado.
        $tenant->run(function () use ($data) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            foreach (RolesSeeder::ROLES as $role) {
                Role::findOrCreate($role, 'web');
            }

            // Plano de contas PGC Angola para a nova empresa.
            (new PgcAngolaSeeder())->run();

            $admin = User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
            ]);
            $admin->assignRole('tenant_admin');

            AuditLog::record('tenant.provisioned', [
                'admin_email' => $data['admin_email'],
                'company' => $data['company_name'],
            ], User::class, $admin->id, $admin->id);
        });

        return $tenant;
    }
}
