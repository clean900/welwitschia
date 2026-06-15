<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({ invoice: Object, payment: Object });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';

const badge = {
    draft: 'bg-slate-600/30 text-slate-300',
    issued: 'bg-amber-500/20 text-amber-300',
    paid: 'bg-emerald-500/20 text-emerald-300',
    cancelled: 'bg-pink-500/20 text-pink-300',
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
                <Link href="/app/invoices" class="text-sm text-slate-500 hover:text-slate-300">← Faturas</Link>
                <h1 class="text-2xl font-extrabold font-mono mt-1 text-white">{{ invoice.number }}</h1>
            </div>
            <span :class="['text-xs font-semibold px-3 py-1 rounded-full', badge[invoice.status]]">{{ label[invoice.status] }}</span>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="md:col-span-2 bg-[#121829] border border-white/5 rounded-2xl p-6">
                <div class="text-sm text-slate-400 mb-4">
                    Cliente: <span class="text-slate-100 font-medium">{{ invoice.customer_name || '—' }}</span>
                    <span v-if="invoice.customer_nif"> · NIF {{ invoice.customer_nif }}</span>
                </div>

                <table class="w-full text-sm mb-4">
                    <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                        <tr>
                            <th class="text-left py-2">Descrição</th>
                            <th class="text-right py-2">Qtd</th>
                            <th class="text-right py-2">Preço</th>
                            <th class="text-right py-2">IVA</th>
                            <th class="text-right py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(it, i) in invoice.items" :key="i" class="border-b border-white/5 text-slate-300">
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
                        <div class="flex justify-between text-slate-400"><span>Subtotal</span><span class="text-slate-200">{{ fmt(invoice.subtotal) }}</span></div>
                        <div class="flex justify-between text-slate-400"><span>IVA</span><span class="text-slate-200">{{ fmt(invoice.iva_amount) }}</span></div>
                        <div class="flex justify-between font-extrabold text-base border-t border-white/10 pt-1 mt-1 text-white"><span>Total</span><span>{{ fmt(invoice.total) }}</span></div>
                    </div>
                </div>
            </div>

            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6 space-y-4">
                <h2 class="font-bold text-white">Ações</h2>

                <template v-if="invoice.status === 'draft'">
                    <button @click="issue" class="w-full py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90">Emitir fatura</button>
                    <button @click="cancel" class="w-full py-2 rounded-lg border border-white/10 text-slate-300 hover:bg-white/5">Cancelar</button>
                </template>

                <template v-else-if="invoice.status === 'issued'">
                    <div v-if="!payment">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Telefone do cliente</label>
                        <input v-model="chargeForm.phone" type="text" class="field mb-1" placeholder="+244 9XX XXX XXX" />
                        <p v-if="chargeForm.errors.phone" class="text-sm text-pink-400 mb-1">{{ chargeForm.errors.phone }}</p>
                        <button @click="charge" :disabled="chargeForm.processing" class="w-full py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90 disabled:opacity-50">
                            {{ chargeForm.processing ? 'A gerar…' : 'Gerar cobrança + SMS' }}
                        </button>
                    </div>
                    <div v-else class="text-sm bg-[#0e1320] border border-white/10 rounded-lg p-3">
                        <div class="text-xs text-slate-500">Referência ProxyPay</div>
                        <div class="font-mono font-bold text-emerald-400">{{ payment.reference }}</div>
                    </div>
                    <button @click="cancel" class="w-full py-2 rounded-lg border border-white/10 text-slate-300 hover:bg-white/5">Cancelar</button>
                </template>

                <template v-else-if="invoice.status === 'paid'">
                    <div class="text-sm bg-emerald-500/10 border border-emerald-500/30 rounded-lg p-3 text-emerald-300">
                        Fatura paga ✓
                        <div v-if="payment" class="font-mono text-xs mt-1">Ref. {{ payment.reference }}</div>
                    </div>
                </template>

                <template v-else>
                    <p class="text-sm text-slate-500">Fatura cancelada.</p>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.field {
    width: 100%;
    padding: 0.45rem 0.65rem;
    background: #0e1320;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    color: #e2e8f0;
    outline: none;
}
.field:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
}
</style>
