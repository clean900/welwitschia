<?php

namespace Database\Seeders\Tenant;

use App\Models\Account;
use Illuminate\Database\Seeder;

/**
 * Plano de contas PGC Angola (subconjunto operacional para o ciclo de cobrança).
 * Corre no schema de cada tenant.
 *
 * // VALIDAR COM CONSULTOR FISCAL AO — códigos, designações e estrutura devem
 * // ser confirmados contra o Plano Geral de Contabilidade (Decreto 82/01).
 */
class PgcAngolaSeeder extends Seeder
{
    public const ACCOUNTS = [
        // Classe 1 — Meios Monetários (disponibilidades)
        ['code' => '11', 'name' => 'Caixa', 'class' => 1, 'type' => 'asset', 'normal_balance' => 'debit'],
        ['code' => '12', 'name' => 'Depósitos à Ordem', 'class' => 1, 'type' => 'asset', 'normal_balance' => 'debit'],

        // Classe 3 — Terceiros
        ['code' => '31', 'name' => 'Clientes', 'class' => 3, 'type' => 'asset', 'normal_balance' => 'debit'],
        ['code' => '32', 'name' => 'Fornecedores', 'class' => 3, 'type' => 'liability', 'normal_balance' => 'credit'],
        ['code' => '33', 'name' => 'Pessoal — Remunerações a Pagar', 'class' => 3, 'type' => 'liability', 'normal_balance' => 'credit'],
        ['code' => '34', 'name' => 'Estado', 'class' => 3, 'type' => 'liability', 'normal_balance' => 'credit', 'is_postable' => false],
        ['code' => '3442', 'name' => 'Estado — IRT Retido', 'class' => 3, 'type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '34'],
        ['code' => '3443', 'name' => 'Estado — IVA Liquidado', 'class' => 3, 'type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '34'],
        ['code' => '35', 'name' => 'Segurança Social (INSS) a Pagar', 'class' => 3, 'type' => 'liability', 'normal_balance' => 'credit'],

        // Classe 6 — Custos e Perdas
        ['code' => '61', 'name' => 'Custo das Mercadorias Vendidas', 'class' => 6, 'type' => 'expense', 'normal_balance' => 'debit'],
        ['code' => '62', 'name' => 'Custos com o Pessoal', 'class' => 6, 'type' => 'expense', 'normal_balance' => 'debit'],

        // Classe 7 — Proveitos e Ganhos
        ['code' => '71', 'name' => 'Vendas e Serviços Prestados', 'class' => 7, 'type' => 'income', 'normal_balance' => 'credit'],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as $account) {
            Account::updateOrCreate(['code' => $account['code']], $account + ['is_postable' => $account['is_postable'] ?? true]);
        }
    }
}
