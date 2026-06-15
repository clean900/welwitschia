<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ invoices: Object });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz';

const badge = {
    draft: 'bg-slate-600/30 text-slate-300',
    issued: 'bg-amber-500/20 text-amber-300',
    paid: 'bg-emerald-500/20 text-emerald-300',
    cancelled: 'bg-pink-500/20 text-pink-300',
};
const label = { draft: 'Rascunho', issued: 'Emitida', paid: 'Paga', cancelled: 'Cancelada' };
</script>

<template>
    <Head title="Faturas" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-extrabold text-white">Faturas</h1>
            <Link href="/app/invoices/criar" class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90">
                Nova fatura
            </Link>
        </div>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr>
                        <th class="text-left px-5 py-3">Número</th>
                        <th class="text-left px-5 py-3">Cliente</th>
                        <th class="text-right px-5 py-3">Total</th>
                        <th class="text-center px-5 py-3">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="inv in invoices.data" :key="inv.id" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-mono text-xs text-slate-300">{{ inv.number }}</td>
                        <td class="px-5 py-3 text-slate-200">{{ inv.customer_name || '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-white">{{ fmt(inv.total) }}</td>
                        <td class="px-5 py-3 text-center">
                            <span :class="['text-xs font-semibold px-2.5 py-0.5 rounded-full', badge[inv.status]]">{{ label[inv.status] }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <Link :href="`/app/invoices/${inv.id}`" class="text-emerald-400 hover:underline">Abrir</Link>
                        </td>
                    </tr>
                    <tr v-if="invoices.data.length === 0">
                        <td colspan="5" class="px-5 py-12 text-center text-slate-500">Ainda não há faturas. Crie a primeira.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="invoices.prev_page_url || invoices.next_page_url" class="flex justify-between mt-4 text-sm">
            <button :disabled="!invoices.prev_page_url" @click="router.get(invoices.prev_page_url)" class="px-3 py-1.5 rounded-md border border-white/10 text-slate-300 disabled:opacity-40">Anterior</button>
            <button :disabled="!invoices.next_page_url" @click="router.get(invoices.next_page_url)" class="px-3 py-1.5 rounded-md border border-white/10 text-slate-300 disabled:opacity-40">Seguinte</button>
        </div>
    </AppLayout>
</template>
