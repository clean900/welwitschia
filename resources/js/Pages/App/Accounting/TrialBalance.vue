<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ totals: Object, accounts: Array });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz';
const balanced = Math.abs((props.totals.debit ?? 0) - (props.totals.credit ?? 0)) < 0.01;
</script>

<template>
    <Head title="Balancete" />
    <AppLayout>
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-2xl font-extrabold text-white">Contabilidade</h1>
            <a href="/app/saft" class="text-sm px-3 py-1.5 rounded-lg border border-white/10 text-slate-300 hover:bg-white/5">↓ SAF-T (AO)</a>
        </div>
        <div class="flex gap-2 mb-6 text-sm">
            <Link href="/app/contabilidade" class="px-3 py-1.5 rounded-lg bg-emerald-500/15 text-emerald-300 font-medium">Balancete</Link>
            <Link href="/app/contabilidade/razao" class="px-3 py-1.5 rounded-lg text-slate-400 hover:bg-white/5">Razão</Link>
        </div>

        <div :class="['mb-6 rounded-xl px-4 py-3 text-sm border', balanced ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-pink-500/10 border-pink-500/30 text-pink-300']">
            <strong>Balancete {{ balanced ? 'equilibrado ✓' : 'DESEQUILIBRADO' }}</strong> —
            Débito {{ fmt(totals.debit) }} · Crédito {{ fmt(totals.credit) }}
        </div>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr><th class="text-left px-5 py-3">Conta</th><th class="text-left px-5 py-3">Designação</th><th class="text-right px-5 py-3">Saldo</th></tr>
                </thead>
                <tbody>
                    <tr v-for="a in accounts" :key="a.code" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-mono text-slate-300">{{ a.code }}</td>
                        <td class="px-5 py-3 text-slate-200">{{ a.name }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-white">{{ fmt(a.balance) }}</td>
                    </tr>
                    <tr v-if="accounts.length === 0"><td colspan="3" class="px-5 py-12 text-center text-slate-500">Sem movimentos contabilísticos.</td></tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-slate-600 mt-3">Plano de contas PGC Angola — a validar com consultor fiscal AO.</p>
    </AppLayout>
</template>
