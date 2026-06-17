<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ customers: Object });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';
const showForm = ref(false);

const form = useForm({ name: '', nif: '', email: '', phone: '', address: '', credit_limit: 0 });

function submit() {
    form.post('/app/clientes', { onSuccess: () => { form.reset(); showForm.value = false; } });
}
function remove(id) {
    if (confirm('Remover este cliente?')) router.delete(`/app/clientes/${id}`);
}
</script>

<template>
    <Head title="Clientes" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-extrabold text-white">Clientes</h1>
            <button @click="showForm = !showForm" class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90">
                {{ showForm ? 'Fechar' : 'Novo cliente' }}
            </button>
        </div>

        <form v-if="showForm" @submit.prevent="submit" class="bg-[#121829] border border-white/5 rounded-2xl p-5 mb-6 grid md:grid-cols-3 gap-3">
            <div><label class="lbl">Nome</label><input v-model="form.name" class="field" /><p v-if="form.errors.name" class="err">{{ form.errors.name }}</p></div>
            <div><label class="lbl">NIF</label><input v-model="form.nif" class="field" /></div>
            <div><label class="lbl">Email</label><input v-model="form.email" type="email" class="field" /><p v-if="form.errors.email" class="err">{{ form.errors.email }}</p></div>
            <div><label class="lbl">Telefone</label><input v-model="form.phone" class="field" /></div>
            <div><label class="lbl">Morada</label><input v-model="form.address" class="field" /></div>
            <div><label class="lbl">Limite de crédito</label><input v-model.number="form.credit_limit" type="number" step="0.01" class="field" /></div>
            <div class="md:col-span-3 flex justify-end"><button type="submit" :disabled="form.processing" class="px-5 py-2 rounded-lg bg-emerald-600 text-white font-semibold disabled:opacity-50">Guardar</button></div>
        </form>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr><th class="text-left px-5 py-3">Nome</th><th class="text-left px-5 py-3">NIF</th><th class="text-left px-5 py-3">Contacto</th><th class="text-right px-5 py-3">Limite crédito</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody>
                    <tr v-for="c in customers.data" :key="c.id" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 text-slate-100">{{ c.name }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ c.nif || '—' }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ c.email || c.phone || '—' }}</td>
                        <td class="px-5 py-3 text-right text-slate-200">{{ fmt(c.credit_limit) }}</td>
                        <td class="px-5 py-3 text-right"><button @click="remove(c.id)" class="text-slate-500 hover:text-pink-400 text-xs">Remover</button></td>
                    </tr>
                    <tr v-if="customers.data.length === 0"><td colspan="5" class="px-5 py-12 text-center text-slate-500">Sem clientes. Adicione o primeiro.</td></tr>
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
