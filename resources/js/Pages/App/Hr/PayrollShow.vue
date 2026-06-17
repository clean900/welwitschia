<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ payroll: Object, payslips: Array });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz';
const MONTHS = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
</script>

<template>
    <Head title="Recibos" />
    <AppLayout>
        <Link href="/app/salarios" class="text-sm text-slate-500 hover:text-slate-300">← Salários</Link>
        <h1 class="text-2xl font-extrabold text-white mt-1 mb-6">Folha de {{ MONTHS[payroll.month - 1] }} {{ payroll.year }}</h1>

        <div class="grid gap-4 md:grid-cols-4 mb-6">
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-5"><div class="text-xs text-slate-400 uppercase">Bruto</div><div class="text-xl font-extrabold text-white mt-1">{{ fmt(payroll.total_gross) }}</div></div>
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-5"><div class="text-xs text-slate-400 uppercase">INSS</div><div class="text-xl font-extrabold text-white mt-1">{{ fmt(payroll.total_inss) }}</div></div>
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-5"><div class="text-xs text-slate-400 uppercase">IRT</div><div class="text-xl font-extrabold text-white mt-1">{{ fmt(payroll.total_irt) }}</div></div>
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-5"><div class="text-xs text-slate-400 uppercase">Líquido</div><div class="text-xl font-extrabold text-emerald-400 mt-1">{{ fmt(payroll.total_net) }}</div></div>
        </div>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr><th class="text-left px-5 py-3">Colaborador</th><th class="text-right px-5 py-3">Bruto</th><th class="text-right px-5 py-3">INSS (3%)</th><th class="text-right px-5 py-3">IRT</th><th class="text-right px-5 py-3">Líquido</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody>
                    <tr v-for="(s, i) in payslips" :key="i" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 text-slate-100">{{ s.employee }}</td>
                        <td class="px-5 py-3 text-right text-slate-200">{{ fmt(s.gross) }}</td>
                        <td class="px-5 py-3 text-right text-slate-400">{{ fmt(s.inss) }}</td>
                        <td class="px-5 py-3 text-right text-slate-400">{{ fmt(s.irt) }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-emerald-400">{{ fmt(s.net) }}</td>
                        <td class="px-5 py-3 text-right"><a :href="`/app/recibos/${s.id}/pdf`" class="text-emerald-400 hover:underline text-xs">Recibo PDF</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-slate-600 mt-3">Tabelas IRT/INSS a validar com consultor fiscal AO.</p>
    </AppLayout>
</template>
