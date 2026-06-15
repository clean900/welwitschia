<?php

namespace Database\Seeders\Landlord;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * 4 planos SaaS base (schema landlord). Preços em AOA.
 */
class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter', 'slug' => 'starter',
                'description' => 'Facturação, ProxyPay, TelcoSMS, Cobrança, Clientes, Stock básico',
                'price_monthly' => 15000, 'price_annual' => 12000,
                'max_users' => 3, 'storage_limit_mb' => 5120, 'trial_days' => 14,
                'sort_order' => 1,
                'features' => ['facturacao', 'proxypay', 'telcosms', 'cobranca', 'clientes', 'stock_basico'],
            ],
            [
                'name' => 'Business', 'slug' => 'business',
                'description' => '+ PGC AO, Contabilidade, RH/Salários, Compras, Encomendas, n8n (10 flows)',
                'price_monthly' => 45000, 'price_annual' => 36000,
                'max_users' => 15, 'storage_limit_mb' => 20480, 'trial_days' => 14,
                'sort_order' => 2,
                'features' => ['pgc_ao', 'contabilidade', 'rh_salarios', 'compras', 'encomendas', 'n8n_10'],
            ],
            [
                'name' => 'Enterprise', 'slug' => 'enterprise',
                'description' => '+ Biometria, VoIP/GoIP, Video Call, Tarefas Kanban, Projectos, BI Dashboard',
                'price_monthly' => 120000, 'price_annual' => 96000,
                'max_users' => 50, 'storage_limit_mb' => 102400, 'trial_days' => 14,
                'sort_order' => 3,
                'features' => ['biometria', 'voip_goip', 'video_call', 'kanban', 'projectos', 'bi_dashboard'],
            ],
            [
                'name' => 'Unlimited', 'slug' => 'unlimited',
                'description' => 'Todos os módulos + IA completa + White-label. Sob consulta.',
                'price_monthly' => 0, 'price_annual' => 0,
                'max_users' => 0, 'storage_limit_mb' => 0, 'trial_days' => 0,
                'sort_order' => 4,
                'features' => ['all', 'ia_completa', 'white_label'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
