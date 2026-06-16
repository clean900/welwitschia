<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ payments: Object, summary: Object });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';

const badge = {
    CREATED: 'bg-slate-600/30 text-slate-300',
    PENDING: 'bg-amber-500/20 text-amber-300',
    PAID: 'bg-blue-500/20 text-blue-300',
    RECONCILED: 'bg-emerald-500/20 text-emerald-300',
    REJECTED: 'bg-pink-500/20 text-pink-300',
    EXPIRED: 'bg-slate-600/30 text-slate-400',
    MANUAL_REVIEW: 'bg-amber-500/20 text-amber-300',
};
const label = {
    CREATED: 'Criada',
    PENDING: 'Pendente',
    PAID: 'Paga',
    RECONCILED: 'Reconciliada',
    REJECTED: 'Rejeitada',
    EXPIRED: 'Expirada',
    MANUAL_REVIEW: 'Revisão manual',
};
</script>

<template>
    <Head title="Cobranças" />
    <AppLayout>
        <h1 class="text-2xl font-extrabold text-white mb-6">Cobranças</h1>

        <div class="grid gap-4 md:grid-cols-2 mb-6">
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-5">
                <div class="text-xs text-slate-400 uppercase tracking-wide">Reconciliado</div>
                <div class="text-2xl font-extrabold text-emerald-400 mt-1">{{ fmt(summary.reconciled) }}</div>
            </div>
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-5">
                <div class="text-xs text-slate-400 uppercase tracking-wide">Por reconciliar</div>
                <div class="text-2xl font-extrabold text-white mt-1">{{ fmt(summary.pending) }}</div>
            </div>
        </div>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr>
                        <th class="text-left px-5 py-3">Referência</th>
                        <th class="text-left px-5 py-3">Factura</th>
                        <th class="text-right px-5 py-3">Valor</th>
                        <th class="text-center px-5 py-3">Estado</th>
                        <th class="text-right px-5 py-3">Data</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(p, i) in payments.data" :key="i" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-mono text-slate-300">{{ p.reference }}</td>
                        <td class="px-5 py-3 text-slate-400 font-mono text-xs">{{ p.invoice || '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-white">{{ fmt(p.amount) }}</td>
                        <td class="px-5 py-3 text-center">
                            <span :class="['text-xs font-semibold px-2.5 py-0.5 rounded-full', badge[p.status]]">{{ label[p.status] || p.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-right text-slate-500">{{ p.date }}</td>
                    </tr>
                    <tr v-if="payments.data.length === 0">
                        <td colspan="5" class="px-5 py-12 text-center text-slate-500">Sem cobranças. Gere uma a partir de uma factura emitida.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="payments.prev_page_url || payments.next_page_url" class="flex justify-between mt-4 text-sm">
            <button :disabled="!payments.prev_page_url" @click="router.get(payments.prev_page_url)" class="px-3 py-1.5 rounded-md border border-white/10 text-slate-300 disabled:opacity-40">Anterior</button>
            <button :disabled="!payments.next_page_url" @click="router.get(payments.next_page_url)" class="px-3 py-1.5 rounded-md border border-white/10 text-slate-300 disabled:opacity-40">Seguinte</button>
        </div>
    </AppLayout>
</template>
