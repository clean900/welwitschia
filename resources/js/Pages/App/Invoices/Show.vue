<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({ invoice: Object, payment: Object });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';

const badge = {
    draft: 'bg-stone-100 text-stone-600',
    issued: 'bg-amber-100 text-amber-800',
    paid: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-700',
};
const label = { draft: 'Rascunho', issued: 'Emitida', paid: 'Paga', cancelled: 'Cancelada' };

const chargeForm = useForm({ phone: '' });

function issue() {
    router.post(`/app/invoices/${props.invoice.id}/emitir`);
}
function cancel() {
    if (confirm('Cancelar esta fatura?')) router.post(`/app/invoices/${props.invoice.id}/cancelar`);
}
function charge() {
    chargeForm.post(`/app/invoices/${props.invoice.id}/cobrar`, { onSuccess: () => chargeForm.reset('phone') });
}
</script>

<template>
    <Head :title="invoice.number" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <div>
                <Link href="/app/invoices" class="text-sm text-stone-500 hover:text-stone-800">← Faturas</Link>
                <h1 class="text-2xl font-extrabold font-mono mt-1">{{ invoice.number }}</h1>
            </div>
            <span :class="['text-xs font-semibold px-3 py-1 rounded-full', badge[invoice.status]]">{{ label[invoice.status] }}</span>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <!-- Detalhe -->
            <div class="md:col-span-2 bg-white border border-stone-200 rounded-xl p-6">
                <div class="text-sm text-stone-500 mb-4">
                    Cliente: <span class="text-stone-800 font-medium">{{ invoice.customer_name || '—' }}</span>
                    <span v-if="invoice.customer_nif"> · NIF {{ invoice.customer_nif }}</span>
                </div>

                <table class="w-full text-sm mb-4">
                    <thead class="text-stone-400 text-xs uppercase border-b border-stone-100">
                        <tr>
                            <th class="text-left py-2">Descrição</th>
                            <th class="text-right py-2">Qtd</th>
                            <th class="text-right py-2">Preço</th>
                            <th class="text-right py-2">IVA</th>
                            <th class="text-right py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(it, i) in invoice.items" :key="i" class="border-b border-stone-50">
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
                        <div class="flex justify-between"><span class="text-stone-500">Subtotal</span><span>{{ fmt(invoice.subtotal) }}</span></div>
                        <div class="flex justify-between"><span class="text-stone-500">IVA</span><span>{{ fmt(invoice.iva_amount) }}</span></div>
                        <div class="flex justify-between font-extrabold text-base border-t border-stone-200 pt-1 mt-1"><span>Total</span><span>{{ fmt(invoice.total) }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="bg-white border border-stone-200 rounded-xl p-6 space-y-4">
                <h2 class="font-bold">Ações</h2>

                <template v-if="invoice.status === 'draft'">
                    <button @click="issue" class="w-full py-2 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800">Emitir fatura</button>
                    <button @click="cancel" class="w-full py-2 rounded-md border border-stone-300 hover:bg-stone-50">Cancelar</button>
                </template>

                <template v-else-if="invoice.status === 'issued'">
                    <div v-if="!payment">
                        <label class="block text-sm font-medium mb-1">Telefone do cliente</label>
                        <input v-model="chargeForm.phone" type="text" class="field mb-1" placeholder="+244 9XX XXX XXX" />
                        <p v-if="chargeForm.errors.phone" class="text-sm text-red-600 mb-1">{{ chargeForm.errors.phone }}</p>
                        <button @click="charge" :disabled="chargeForm.processing" class="w-full py-2 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800 disabled:opacity-50">
                            {{ chargeForm.processing ? 'A gerar…' : 'Gerar cobrança + SMS' }}
                        </button>
                    </div>
                    <div v-else class="text-sm bg-stone-50 border border-stone-200 rounded-lg p-3">
                        <div class="text-xs text-stone-400">Referência ProxyPay</div>
                        <div class="font-mono font-bold text-orange-700">{{ payment.reference }}</div>
                    </div>
                    <button @click="cancel" class="w-full py-2 rounded-md border border-stone-300 hover:bg-stone-50">Cancelar</button>
                </template>

                <template v-else-if="invoice.status === 'paid'">
                    <div class="text-sm bg-green-50 border border-green-200 rounded-lg p-3 text-green-800">
                        Fatura paga ✓
                        <div v-if="payment" class="font-mono text-xs mt-1">Ref. {{ payment.reference }}</div>
                    </div>
                </template>

                <template v-else>
                    <p class="text-sm text-stone-400">Fatura cancelada.</p>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.field {
    width: 100%;
    padding: 0.45rem 0.65rem;
    border: 1px solid #d6d3d1;
    border-radius: 0.375rem;
    outline: none;
}
.field:focus {
    border-color: #ea580c;
    box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.35);
}
</style>
