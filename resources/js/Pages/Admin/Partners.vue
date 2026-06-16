<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

defineProps({ partners: Array });

const form = useForm({ name: '', logo_url: '' });

function add() {
    form.post('/admin/parceiros', { onSuccess: () => form.reset() });
}
function toggle(id) {
    router.post(`/admin/parceiros/${id}/toggle`);
}
function remove(id) {
    if (confirm('Remover este parceiro?')) router.delete(`/admin/parceiros/${id}`);
}
</script>

<template>
    <Head title="Parceiros da landing" />
    <AdminLayout>
        <h1 class="text-2xl font-extrabold text-white mb-2">Parceiros da landing</h1>
        <p class="text-slate-500 text-sm mb-6">Empresas exibidas no “empresas que confiam” da página pública.</p>

        <form @submit.prevent="add" class="bg-[#121829] border border-white/5 rounded-2xl p-5 mb-6 grid md:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-sm text-slate-300 mb-1">Nome</label>
                <input v-model="form.name" class="field" placeholder="Empresa XYZ" />
                <p v-if="form.errors.name" class="text-sm text-pink-400 mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1">URL do logo (opcional)</label>
                <input v-model="form.logo_url" class="field" placeholder="https://…/logo.png" />
                <p v-if="form.errors.logo_url" class="text-sm text-pink-400 mt-1">{{ form.errors.logo_url }}</p>
            </div>
            <button type="submit" :disabled="form.processing" class="py-2 rounded-lg bg-gradient-to-r from-pink-500 to-pink-600 text-white font-semibold disabled:opacity-50">Adicionar</button>
        </form>

        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr><th class="text-left px-5 py-3">Parceiro</th><th class="text-left px-5 py-3">Logo</th><th class="text-center px-5 py-3">Visível</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody>
                    <tr v-for="p in partners" :key="p.id" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 text-slate-100">{{ p.name }}</td>
                        <td class="px-5 py-3">
                            <img v-if="p.logo_url" :src="p.logo_url" :alt="p.name" class="h-6 w-auto object-contain" />
                            <span v-else class="text-slate-600 text-xs">só nome</span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <button @click="toggle(p.id)" :class="['text-xs px-2 py-0.5 rounded-full', p.active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-600/30 text-slate-400']">
                                {{ p.active ? 'Visível' : 'Oculto' }}
                            </button>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button @click="remove(p.id)" class="text-pink-400 hover:underline text-xs">Remover</button>
                        </td>
                    </tr>
                    <tr v-if="partners.length === 0"><td colspan="4" class="px-5 py-12 text-center text-slate-500">Sem parceiros. Adicione o primeiro.</td></tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<style scoped>
.field { width:100%; padding:.5rem .7rem; background:#0e1320; border:1px solid rgba(255,255,255,.1); border-radius:.5rem; color:#e2e8f0; outline:none; }
.field:focus { border-color:#ec4899; box-shadow:0 0 0 2px rgba(236,72,153,.3); }
</style>
