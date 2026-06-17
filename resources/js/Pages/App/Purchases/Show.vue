<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ order: Object });
const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';
const badge = { draft: 'bg-slate-600/30 text-slate-300', confirmed: 'bg-amber-500/20 text-amber-300', received: 'bg-emerald-500/20 text-emerald-300', cancelled: 'bg-pink-500/20 text-pink-300' };
const label = { draft: 'Rascunho', confirmed: 'Confirmada', received: 'Recebida', cancelled: 'Cancelada' };

function confirmOrder() { router.post(`/app/compras/${props.order.id}/confirmar`); }
function receive() { router.post(`/app/compras/${props.order.id}/receber`); }
</script>

<template>
    <Head :title="order.number" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <div>
                <Link href="/app/compras" class="text-sm text-slate-500 hover:text-slate-300">← Compras</Link>
                <h1 class="text-2xl font-extrabold font-mono mt-1 text-white">{{ order.number }}</h1>
            </div>
            <span :class="['text-xs font-semibold px-3 py-1 rounded-full', badge[order.status]]">{{ label[order.status] }}</span>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="md:col-span-2 bg-[#121829] border border-white/5 rounded-2xl p-6">
                <div class="text-sm text-slate-400 mb-4">Fornecedor: <span class="text-slate-100 font-medium">{{ order.supplier_name || '—' }}</span></div>
                <table class="w-full text-sm mb-4">
                    <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                        <tr><th class="text-left py-2">Descrição</th><th class="text-right py-2">Qtd</th><th class="text-right py-2">Custo</th><th class="text-right py-2">IVA</th><th class="text-right py-2">Total</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="(it, i) in order.items" :key="i" class="border-b border-white/5 text-slate-300">
                            <td class="py-2">{{ it.description }}</td>
                            <td class="py-2 text-right">{{ it.quantity }}</td>
                            <td class="py-2 text-right">{{ fmt(it.unit_price) }}</td>
                            <td class="py-2 text-right">{{ it.iva_rate }}%</td>
                            <td class="py-2 text-right">{{ fmt(it.line_total) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex justify-end">
                    <div class="w-56 space-y-1 text-sm">
                        <div class="flex justify-between text-slate-400"><span>Subtotal</span><span class="text-slate-200">{{ fmt(order.subtotal) }}</span></div>
                        <div class="flex justify-between text-slate-400"><span>IVA</span><span class="text-slate-200">{{ fmt(order.iva_amount) }}</span></div>
                        <div class="flex justify-between font-extrabold text-base border-t border-white/10 pt-1 mt-1 text-white"><span>Total</span><span>{{ fmt(order.total) }}</span></div>
                    </div>
                </div>
            </div>

            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6 space-y-4">
                <h2 class="font-bold text-white">Ações</h2>
                <template v-if="order.status === 'draft'">
                    <button @click="confirmOrder" class="w-full py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90">Confirmar ordem</button>
                </template>
                <template v-else-if="order.status === 'confirmed'">
                    <button @click="receive" class="w-full py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90">Receber (entrada de stock)</button>
                </template>
                <template v-else-if="order.status === 'received'">
                    <div class="text-sm bg-emerald-500/10 border border-emerald-500/30 rounded-lg p-3 text-emerald-300">Recebida ✓ — stock actualizado</div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
