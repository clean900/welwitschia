<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ orders: Object });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz';
const badge = {
    draft: 'bg-slate-600/30 text-slate-300',
    confirmed: 'bg-amber-500/20 text-amber-300',
    invoiced: 'bg-emerald-500/20 text-emerald-300',
    cancelled: 'bg-pink-500/20 text-pink-300',
};
const label = { draft: 'Rascunho', confirmed: 'Confirmada', invoiced: 'Faturada', cancelled: 'Cancelada' };
</script>

<template>
    <Head title="Vendas" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-extrabold text-white">Vendas</h1>
            <Link href="/app/vendas/criar" class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90">Nova encomenda</Link>
        </div>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr><th class="text-left px-5 py-3">Número</th><th class="text-left px-5 py-3">Cliente</th><th class="text-right px-5 py-3">Total</th><th class="text-center px-5 py-3">Estado</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody>
                    <tr v-for="o in orders.data" :key="o.id" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 font-mono text-xs text-slate-300">{{ o.number }}</td>
                        <td class="px-5 py-3 text-slate-200">{{ o.customer || '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-white">{{ fmt(o.total) }}</td>
                        <td class="px-5 py-3 text-center"><span :class="['text-xs font-semibold px-2.5 py-0.5 rounded-full', badge[o.status]]">{{ label[o.status] }}</span></td>
                        <td class="px-5 py-3 text-right"><Link :href="`/app/vendas/${o.id}`" class="text-emerald-400 hover:underline">Abrir</Link></td>
                    </tr>
                    <tr v-if="orders.data.length === 0"><td colspan="5" class="px-5 py-12 text-center text-slate-500">Sem encomendas. Crie a primeira.</td></tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
