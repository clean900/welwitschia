<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ suppliers: Array, products: Array });

const form = useForm({
    supplier_id: '',
    items: [{ product_id: '', description: '', quantity: 1, unit_price: 0, iva_rate: 14 }],
});

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';

function addItem() { form.items.push({ product_id: '', description: '', quantity: 1, unit_price: 0, iva_rate: 14 }); }
function removeItem(i) { if (form.items.length > 1) form.items.splice(i, 1); }
function onProduct(i) {
    const p = props.products.find((x) => x.id === Number(form.items[i].product_id));
    if (p) { form.items[i].description = p.name; form.items[i].unit_price = Number(p.price); }
}
const totals = computed(() => {
    let s = 0, iva = 0;
    for (const it of form.items) { const line = (Number(it.quantity) || 0) * (Number(it.unit_price) || 0); s += line; iva += line * ((Number(it.iva_rate) || 0) / 100); }
    return { subtotal: s, iva, total: s + iva };
});
function submit() { form.transform((d) => ({ ...d, supplier_id: d.supplier_id || null })).post('/app/compras'); }
</script>

<template>
    <Head title="Nova ordem de compra" />
    <AppLayout>
        <h1 class="text-2xl font-extrabold text-white mb-6">Nova ordem de compra</h1>
        <form @submit.prevent="submit" class="space-y-6">
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6">
                <label class="block text-sm text-slate-300 mb-1">Fornecedor</label>
                <select v-model="form.supplier_id" class="field md:w-1/2">
                    <option value="">— Sem fornecedor registado —</option>
                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>

            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-bold text-white">Linhas</h2>
                    <button type="button" @click="addItem" class="text-sm text-emerald-400 font-medium">+ Adicionar linha</button>
                </div>
                <div class="space-y-3">
                    <div v-for="(it, i) in form.items" :key="i" class="grid grid-cols-12 gap-2 items-center">
                        <select v-model="it.product_id" @change="onProduct(i)" class="field col-span-3">
                            <option value="">Livre…</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <input v-model="it.description" type="text" placeholder="Descrição" class="field col-span-3" />
                        <input v-model.number="it.quantity" type="number" step="0.01" placeholder="Qtd" class="field col-span-2" />
                        <input v-model.number="it.unit_price" type="number" step="0.01" placeholder="Custo" class="field col-span-2" />
                        <div class="col-span-1 flex items-center gap-1"><input v-model.number="it.iva_rate" type="number" class="field" /><span class="text-xs text-slate-500">%</span></div>
                        <button type="button" @click="removeItem(i)" class="col-span-1 text-slate-500 hover:text-pink-400 text-center">✕</button>
                    </div>
                </div>
                <p v-if="form.errors.items" class="text-sm text-pink-400 mt-2">{{ form.errors.items }}</p>
            </div>

            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6 flex justify-end">
                <div class="w-64 space-y-1 text-sm">
                    <div class="flex justify-between text-slate-400"><span>Subtotal</span><span class="text-slate-200">{{ fmt(totals.subtotal) }}</span></div>
                    <div class="flex justify-between text-slate-400"><span>IVA</span><span class="text-slate-200">{{ fmt(totals.iva) }}</span></div>
                    <div class="flex justify-between font-extrabold text-base border-t border-white/10 pt-1 mt-1 text-white"><span>Total</span><span>{{ fmt(totals.total) }}</span></div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="px-6 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90 disabled:opacity-50">
                    {{ form.processing ? 'A criar…' : 'Criar ordem' }}
                </button>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped>
.field { width:100%; padding:.45rem .65rem; background:#0e1320; border:1px solid rgba(255,255,255,.1); border-radius:.5rem; color:#e2e8f0; outline:none; }
.field:focus { border-color:#10b981; box-shadow:0 0 0 2px rgba(16,185,129,.3); }
</style>
