<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ invoices: Object });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz';

const badge = {
    draft: 'bg-stone-100 text-stone-600',
    issued: 'bg-amber-100 text-amber-800',
    paid: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-700',
};
const label = { draft: 'Rascunho', issued: 'Emitida', paid: 'Paga', cancelled: 'Cancelada' };
</script>

<template>
    <Head title="Faturas" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-extrabold">Faturas</h1>
            <Link href="/app/invoices/criar" class="px-4 py-2 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800">
                Nova fatura
            </Link>
        </div>

        <div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-stone-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Número</th>
                        <th class="text-left px-4 py-3">Cliente</th>
                        <th class="text-right px-4 py-3">Total</th>
                        <th class="text-center px-4 py-3">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="inv in invoices.data" :key="inv.id" class="border-t border-stone-100 hover:bg-stone-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ inv.number }}</td>
                        <td class="px-4 py-3">{{ inv.customer_name || '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ fmt(inv.total) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', badge[inv.status]]">{{ label[inv.status] }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="`/app/invoices/${inv.id}`" class="text-orange-700 hover:underline">Abrir</Link>
                        </td>
                    </tr>
                    <tr v-if="invoices.data.length === 0">
                        <td colspan="5" class="px-4 py-10 text-center text-stone-400">Ainda não há faturas. Crie a primeira.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="invoices.prev_page_url || invoices.next_page_url" class="flex justify-between mt-4 text-sm">
            <button :disabled="!invoices.prev_page_url" @click="router.get(invoices.prev_page_url)" class="px-3 py-1.5 rounded-md border border-stone-300 disabled:opacity-40">Anterior</button>
            <button :disabled="!invoices.next_page_url" @click="router.get(invoices.next_page_url)" class="px-3 py-1.5 rounded-md border border-stone-300 disabled:opacity-40">Seguinte</button>
        </div>
    </AppLayout>
</template>
