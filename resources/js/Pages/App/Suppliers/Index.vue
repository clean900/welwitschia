<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ suppliers: Object });
const showForm = ref(false);
const form = useForm({ name: '', nif: '', email: '', phone: '', address: '' });

function submit() {
    form.post('/app/fornecedores', { onSuccess: () => { form.reset(); showForm.value = false; } });
}
function remove(id) {
    if (confirm('Remover este fornecedor?')) router.delete(`/app/fornecedores/${id}`);
}
</script>

<template>
    <Head title="Fornecedores" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-extrabold text-white">Fornecedores</h1>
            <button @click="showForm = !showForm" class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90">{{ showForm ? 'Fechar' : 'Novo fornecedor' }}</button>
        </div>

        <form v-if="showForm" @submit.prevent="submit" class="bg-[#121829] border border-white/5 rounded-2xl p-5 mb-6 grid md:grid-cols-3 gap-3">
            <div><label class="lbl">Nome</label><input v-model="form.name" class="field" /><p v-if="form.errors.name" class="err">{{ form.errors.name }}</p></div>
            <div><label class="lbl">NIF</label><input v-model="form.nif" class="field" /></div>
            <div><label class="lbl">Email</label><input v-model="form.email" type="email" class="field" /></div>
            <div><label class="lbl">Telefone</label><input v-model="form.phone" class="field" /></div>
            <div><label class="lbl">Morada</label><input v-model="form.address" class="field" /></div>
            <div class="flex items-end justify-end"><button type="submit" :disabled="form.processing" class="px-5 py-2 rounded-lg bg-emerald-600 text-white font-semibold disabled:opacity-50">Guardar</button></div>
        </form>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr><th class="text-left px-5 py-3">Nome</th><th class="text-left px-5 py-3">NIF</th><th class="text-left px-5 py-3">Contacto</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody>
                    <tr v-for="s in suppliers.data" :key="s.id" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 text-slate-100">{{ s.name }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ s.nif || '—' }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ s.email || s.phone || '—' }}</td>
                        <td class="px-5 py-3 text-right"><button @click="remove(s.id)" class="text-slate-500 hover:text-pink-400 text-xs">Remover</button></td>
                    </tr>
                    <tr v-if="suppliers.data.length === 0"><td colspan="4" class="px-5 py-12 text-center text-slate-500">Sem fornecedores.</td></tr>
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
