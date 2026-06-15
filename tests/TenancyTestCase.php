<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Base para testes que precisam de PostgreSQL real (schemas, advisory locks).
 * Não usa RefreshDatabase (transacções) porque a criação de schema é DDL.
 */
abstract class TenancyTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->dropTenantSchemas();
        $this->artisan('migrate:fresh', ['--force' => true]);
        tenancy()->end();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        $this->dropTenantSchemas();

        parent::tearDown();
    }

    protected function dropTenantSchemas(): void
    {
        $rows = DB::connection('pgsql')->select(
            "SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE 'tenant_%'"
        );

        foreach ($rows as $row) {
            DB::connection('pgsql')->statement('DROP SCHEMA IF EXISTS "' . $row->schema_name . '" CASCADE');
        }
    }

    protected function makeTenant(string $id): Tenant
    {
        return Tenant::create([
            'id' => $id,
            'name' => ucfirst($id) . ' Lda',
            'slug' => $id,
            'status' => 'active',
        ]);
    }
}
