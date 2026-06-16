<?php

namespace Tests\Feature\Admin;

use App\Models\LandingPartner;
use App\Models\PlatformAdmin;
use Database\Seeders\Landlord\PlansSeeder;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AdminPartnerTest extends TenancyTestCase
{
    private PlatformAdmin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->admin = PlatformAdmin::create([
            'name' => 'Bráulio', 'email' => 'admin@welwitschia.ao', 'password' => Hash::make('password123'),
        ]);
    }

    public function test_pagina_de_parceiros_renderiza(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/parceiros')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Partners')->has('partners'));
    }

    public function test_admin_adiciona_parceiro(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/parceiros', ['name' => 'Banco Exemplo'])
            ->assertRedirect();

        $this->assertDatabaseHas('landing_partners', ['name' => 'Banco Exemplo', 'active' => true]);
    }

    public function test_landing_mostra_parceiros_visiveis(): void
    {
        LandingPartner::create(['name' => 'Empresa Alfa', 'active' => true]);
        LandingPartner::create(['name' => 'Oculta', 'active' => false]);

        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->has('partners', 1)
                ->where('partners.0.name', 'Empresa Alfa')
            );
    }
}
