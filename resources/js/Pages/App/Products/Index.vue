<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ products: Object });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';
const num = (v) => new Intl.NumberFormat('pt-PT').format(v || 0);
const showForm = ref(false);

const form = useForm({ name: '', sku: '', unit: 'un', price: 0, stock_qty: 0, min_stock: 0 });

function submit() {
    form.post('/app/produtos', { onSuccess: () => { form.reset(); showForm.value = false; } });
}
function move(id, type) {
    const q = prompt(type === 'entrada' ? 'Quantidade a dar entrada:' : 'Quantidade a dar saída:');
    if (q && Number(q) > 0) router.post(`/app/produtos/${id}/movimentar`, { type, quantity: Number(q) });
}
function remove(id) {
    if (confirm('Remover este produto?')) router.delete(`/app/produtos/${id}`);
}
</script>

<template>
    <Head title="Stock" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-extrabold text-white">Produtos & Stock</h1>
            <button @click="showForm = !showForm" class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90">
                {{ showForm ? 'Fechar' : 'Novo produto' }}
            </button>
        </div>

        <form v-if="showForm" @submit.prevent="submit" class="bg-[#121829] border border-white/5 rounded-2xl p-5 mb-6 grid md:grid-cols-3 gap-3">
            <div><label class="lbl">Nome</label><input v-model="form.name" class="field" /><p v-if="form.errors.name" class="err">{{ form.errors.name }}</p></div>
            <div><label class="lbl">SKU</label><input v-model="form.sku" class="field" /></div>
            <div><label class="lbl">Unidade</label><input v-model="form.unit" class="field" placeholder="un" /></div>
            <div><label class="lbl">Preço</label><input v-model.number="form.price" type="number" step="0.01" class="field" /></div>
            <div><label class="lbl">Stock inicial</label><input v-model.number="form.stock_qty" type="number" step="0.01" class="field" /></div>
            <div><label class="lbl">Stock mínimo</label><input v-model.number="form.min_stock" type="number" step="0.01" class="field" /></div>
            <div class="md:col-span-3 flex justify-end"><button type="submit" :disabled="form.processing" class="px-5 py-2 rounded-lg bg-emerald-600 text-white font-semibold disabled:opacity-50">Guardar</button></div>
        </form>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr><th class="text-left px-5 py-3">Produto</th><th class="text-left px-5 py-3">SKU</th><th class="text-right px-5 py-3">Preço</th><th class="text-right px-5 py-3">Stock</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody>
                    <tr v-for="p in products.data" :key="p.id" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 text-slate-100">{{ p.name }}</td>
                        <td class="px-5 py-3 text-slate-400 font-mono text-xs">{{ p.sku || '—' }}</td>
                        <td class="px-5 py-3 text-right text-slate-200">{{ fmt(p.price) }}</td>
                        <td class="px-5 py-3 text-right">
                            <span :class="p.low ? 'text-pink-400 font-bold' : 'text-slate-200'">{{ num(p.stock_qty) }} {{ p.unit }}</span>
                            <span v-if="p.low" class="ml-2 text-[10px] uppercase bg-pink-500/20 text-pink-300 px-1.5 py-0.5 rounded-full">baixo</span>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <button @click="move(p.id, 'entrada')" class="text-emerald-400 hover:underline text-xs mr-2">Entrada</button>
                            <button @click="move(p.id, 'saida')" class="text-amber-400 hover:underline text-xs mr-2">Saída</button>
                            <button @click="remove(p.id)" class="text-slate-500 hover:text-pink-400 text-xs">✕</button>
                        </td>
                    </tr>
                    <tr v-if="products.data.length === 0"><td colspan="5" class="px-5 py-12 text-center text-slate-500">Sem produtos. Adicione o primeiro.</td></tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<style scoped>
.lbl { display:block; font-size:.8rem; color:#cbd5e1; margin-bottom:.25rem; }
.field { width:100%; padding:.45rem .65rem; background:#0e1320; border:1px solid rgba(255,255,255,.1); border-radius:.5rem; color:#e2e8f0; outline:none; }
.field:focus { border-color:#10b981; box-shadow:0 0 0 2px rgba(16,185,129,.3); }
.err { font-size:.8rem; color:#f472b6; margin-top:.25rem; }
</style>
