<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    customer_name: '',
    customer_nif: '',
    items: [{ description: '', quantity: 1, unit_price: 0, iva_rate: 14 }],
});

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';

function addItem() {
    form.items.push({ description: '', quantity: 1, unit_price: 0, iva_rate: 14 });
}
function removeItem(i) {
    if (form.items.length > 1) form.items.splice(i, 1);
}

const totals = computed(() => {
    let subtotal = 0, iva = 0;
    for (const it of form.items) {
        const line = (Number(it.quantity) || 0) * (Number(it.unit_price) || 0);
        subtotal += line;
        iva += line * ((Number(it.iva_rate) || 0) / 100);
    }
    return { subtotal, iva, total: subtotal + iva };
});

function submit() {
    form.post('/app/invoices');
}
</script>

<template>
    <Head title="Nova fatura" />
    <AppLayout>
        <h1 class="text-2xl font-extrabold mb-6">Nova fatura</h1>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="bg-white border border-stone-200 rounded-xl p-6 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Cliente</label>
                    <input v-model="form.customer_name" type="text" class="field" placeholder="Nome do cliente" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">NIF (opcional)</label>
                    <input v-model="form.customer_nif" type="text" class="field" />
                </div>
            </div>

            <div class="bg-white border border-stone-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-bold">Linhas</h2>
                    <button type="button" @click="addItem" class="text-sm text-orange-700 font-medium">+ Adicionar linha</button>
                </div>

                <div class="space-y-3">
                    <div v-for="(it, i) in form.items" :key="i" class="grid grid-cols-12 gap-2 items-center">
                        <input v-model="it.description" type="text" placeholder="Descrição" class="field col-span-5" />
                        <input v-model.number="it.quantity" type="number" step="0.01" placeholder="Qtd" class="field col-span-2" />
                        <input v-model.number="it.unit_price" type="number" step="0.01" placeholder="Preço" class="field col-span-2" />
                        <div class="col-span-2 flex items-center gap-1">
                            <input v-model.number="it.iva_rate" type="number" step="1" class="field" /> <span class="text-xs text-stone-400">%</span>
                        </div>
                        <button type="button" @click="removeItem(i)" class="col-span-1 text-stone-400 hover:text-red-600 text-center">✕</button>
                    </div>
                </div>
                <p v-if="form.errors.items" class="text-sm text-red-600 mt-2">{{ form.errors.items }}</p>
            </div>

            <div class="bg-white border border-stone-200 rounded-xl p-6 flex justify-end">
                <div class="w-64 space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-stone-500">Subtotal</span><span>{{ fmt(totals.subtotal) }}</span></div>
                    <div class="flex justify-between"><span class="text-stone-500">IVA</span><span>{{ fmt(totals.iva) }}</span></div>
                    <div class="flex justify-between font-extrabold text-base border-t border-stone-200 pt-1 mt-1"><span>Total</span><span>{{ fmt(totals.total) }}</span></div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" :disabled="form.processing" class="px-6 py-2 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800 disabled:opacity-50">
                    {{ form.processing ? 'A criar…' : 'Criar rascunho' }}
                </button>
            </div>
        </form>
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
