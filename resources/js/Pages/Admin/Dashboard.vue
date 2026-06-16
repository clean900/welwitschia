<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({ metrics: Object, byPlan: Array, companies: Array });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';

const badge = {
    active: 'bg-emerald-500/20 text-emerald-300',
    trial: 'bg-blue-500/20 text-blue-300',
    suspended: 'bg-pink-500/20 text-pink-300',
};
const label = { active: 'Activa', trial: 'Trial', suspended: 'Suspensa' };

function toggle(id) {
    router.post(`/admin/empresas/${id}/suspender`);
}
</script>

<template>
    <Head title="Plataforma — Painel" />
    <AdminLayout>
        <h1 class="text-2xl font-extrabold text-white mb-6">Painel da Plataforma</h1>

        <!-- Métricas -->
        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-5">
                <div class="text-xs text-slate-400 uppercase tracking-wide">Empresas</div>
                <div class="text-2xl font-extrabold text-white mt-1">{{ metrics.companies }}</div>
            </div>
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-5">
                <div class="text-xs text-slate-400 uppercase tracking-wide">MRR estimado</div>
                <div class="text-2xl font-extrabold text-emerald-400 mt-1">{{ fmt(metrics.mrr) }}</div>
            </div>
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-5">
                <div class="text-xs text-slate-400 uppercase tracking-wide">Contas de utilizador</div>
                <div class="text-2xl font-extrabold text-white mt-1">{{ metrics.memberships }}</div>
            </div>
        </div>

        <!-- Por plano -->
        <div v-if="byPlan.length" class="flex flex-wrap gap-3 mb-6">
            <div v-for="p in byPlan" :key="p.plan" class="bg-[#121829] border border-white/5 rounded-xl px-4 py-2 text-sm">
                <span class="text-slate-400">{{ p.plan }}:</span> <span class="text-white font-bold">{{ p.count }}</span>
            </div>
        </div>

        <!-- Empresas -->
        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr>
                        <th class="text-left px-5 py-3">Empresa</th>
                        <th class="text-left px-5 py-3">Plano</th>
                        <th class="text-center px-5 py-3">Estado</th>
                        <th class="text-right px-5 py-3">Criada</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in companies" :key="c.id" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 text-slate-100">{{ c.name }} <span class="text-slate-600 font-mono text-xs">{{ c.id }}</span></td>
                        <td class="px-5 py-3 text-slate-400">{{ c.plan }}</td>
                        <td class="px-5 py-3 text-center">
                            <span :class="['text-xs px-2 py-0.5 rounded-full', badge[c.status] || 'bg-slate-600/30 text-slate-300']">{{ label[c.status] || c.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-right text-slate-500">{{ c.created_at }}</td>
                        <td class="px-5 py-3 text-right">
                            <button @click="toggle(c.id)" :class="['text-xs hover:underline', c.status === 'suspended' ? 'text-emerald-400' : 'text-pink-400']">
                                {{ c.status === 'suspended' ? 'Reactivar' : 'Suspender' }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="companies.length === 0"><td colspan="5" class="px-5 py-12 text-center text-slate-500">Sem empresas registadas.</td></tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
