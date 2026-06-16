<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ employees: Object });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz';
const showForm = ref(false);

const form = useForm({ name: '', position: '', base_salary: null, allowances: 0, nif: '', phone: '' });

function submit() {
    form.post('/app/colaboradores', { onSuccess: () => { form.reset(); showForm.value = false; } });
}
function terminate(id) {
    if (confirm('Desactivar este colaborador?')) router.delete(`/app/colaboradores/${id}`);
}
</script>

<template>
    <Head title="Colaboradores" />
    <AppLayout>
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-2xl font-extrabold text-white">RH & Salários</h1>
            <button @click="showForm = !showForm" class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90">
                {{ showForm ? 'Fechar' : 'Adicionar colaborador' }}
            </button>
        </div>
        <div class="flex gap-2 mb-6 text-sm">
            <Link href="/app/colaboradores" class="px-3 py-1.5 rounded-lg bg-emerald-500/15 text-emerald-300 font-medium">Colaboradores</Link>
            <Link href="/app/salarios" class="px-3 py-1.5 rounded-lg text-slate-400 hover:bg-white/5">Salários</Link>
        </div>

        <form v-if="showForm" @submit.prevent="submit" class="bg-[#121829] border border-white/5 rounded-2xl p-5 mb-6 grid md:grid-cols-3 gap-3">
            <div><label class="lbl">Nome</label><input v-model="form.name" class="field" /><p v-if="form.errors.name" class="err">{{ form.errors.name }}</p></div>
            <div><label class="lbl">Função</label><input v-model="form.position" class="field" /></div>
            <div><label class="lbl">NIF</label><input v-model="form.nif" class="field" /></div>
            <div><label class="lbl">Salário base</label><input v-model.number="form.base_salary" type="number" step="0.01" class="field" /><p v-if="form.errors.base_salary" class="err">{{ form.errors.base_salary }}</p></div>
            <div><label class="lbl">Subsídios</label><input v-model.number="form.allowances" type="number" step="0.01" class="field" /></div>
            <div class="flex items-end"><button type="submit" :disabled="form.processing" class="w-full py-2 rounded-lg bg-emerald-600 text-white font-semibold disabled:opacity-50">Guardar</button></div>
        </form>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr><th class="text-left px-5 py-3">Nome</th><th class="text-left px-5 py-3">Função</th><th class="text-right px-5 py-3">Base</th><th class="text-right px-5 py-3">Subsídios</th><th class="text-center px-5 py-3">Estado</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody>
                    <tr v-for="e in employees.data" :key="e.id" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 text-slate-100">{{ e.name }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ e.position || '—' }}</td>
                        <td class="px-5 py-3 text-right text-slate-200">{{ fmt(e.base_salary) }}</td>
                        <td class="px-5 py-3 text-right text-slate-400">{{ fmt(e.allowances) }}</td>
                        <td class="px-5 py-3 text-center">
                            <span :class="['text-xs px-2 py-0.5 rounded-full', e.status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-600/30 text-slate-400']">{{ e.status === 'active' ? 'Activo' : 'Inactivo' }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button v-if="e.status === 'active'" @click="terminate(e.id)" class="text-slate-500 hover:text-pink-400 text-xs">Desactivar</button>
                        </td>
                    </tr>
                    <tr v-if="employees.data.length === 0"><td colspan="6" class="px-5 py-12 text-center text-slate-500">Sem colaboradores. Adicione o primeiro.</td></tr>
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
