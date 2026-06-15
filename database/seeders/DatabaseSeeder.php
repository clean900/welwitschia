<?php

namespace Database\Seeders;

use Database\Seeders\Landlord\PlansSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database (schema landlord/central).
     */
    public function run(): void
    {
        // Central/landlord apenas. As roles são semeadas POR tenant (schema isolado)
        // durante o provisionamento — ver TenantProvisioningService.
        $this->call([
            PlansSeeder::class,
        ]);
    }
}
