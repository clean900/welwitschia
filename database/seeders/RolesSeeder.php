<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Roles base da plataforma e dos tenants (guard 'web').
 */
class RolesSeeder extends Seeder
{
    public const ROLES = [
        'super_admin',      // plataforma Welwitschia
        'tenant_admin',     // administrador da empresa cliente
        'financial_admin',  // gestor financeiro
        'accountant',       // contabilista
        'hr_manager',       // gestor RH
        'hr_staff',         // colaborador RH
        'sales',            // vendedor
        'warehouse',        // armazém
        'readonly',         // apenas leitura
    ];

    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'tenant');
        }
    }
}
