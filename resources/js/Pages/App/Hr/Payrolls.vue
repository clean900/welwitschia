<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ payrolls: Array, currentYear: Number, currentMonth: Number, employeesCount: Number });

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz';
const MONTHS = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

const form = useForm({ year: props.currentYear, month: props.currentMonth });

function process() {
    form.post('/app/salarios');
}
</script>

<template>
    <Head title="Salários" />
    <AppLayout>
        <h1 class="text-2xl font-extrabold text-white mb-2">RH & Salários</h1>
        <div class="flex gap-2 mb-6 text-sm">
            <Link href="/app/colaboradores" class="px-3 py-1.5 rounded-lg text-slate-400 hover:bg-white/5">Colaboradores</Link>
            <Link href="/app/salarios" class="px-3 py-1.5 rounded-lg bg-emerald-500/15 text-emerald-300 font-medium">Salários</Link>
        </div>

        <!-- Processar -->
        <div class="bg-[#121829] border border-white/5 rounded-2xl p-6 mb-6">
            <h2 class="font-bold text-white mb-1">Processar folha</h2>
            <p class="text-sm text-slate-400 mb-4">{{ employeesCount }} colaborador(es) activo(s). IRT e INSS calculados automaticamente.</p>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="lbl">Mês</label>
                    <select v-model.number="form.month" class="field w-40">
                        <option v-for="(m, i) in MONTHS" :key="i" :value="i + 1">{{ m }}</option>
                    </select>
                </div>
                <div>
                    <label class="lbl">Ano</label>
                    <input v-model.number="form.year" type="number" class="field w-28" />
                </div>
                <button @click="process" :disabled="form.processing" class="py-2 px-5 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90 disabled:opacity-50">
                    {{ form.processing ? 'A processar…' : 'Processar' }}
                </button>
            </div>
            <p v-if="form.errors.period" class="text-sm text-pink-400 mt-2">{{ form.errors.period }}</p>
        </div>

        <!-- Histórico -->
        <div class="bg-[#121829] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs uppercase border-b border-white/5">
                    <tr><th class="text-left px-5 py-3">Período</th><th class="text-right px-5 py-3">Bruto</th><th class="text-right px-5 py-3">INSS</th><th class="text-right px-5 py-3">IRT</th><th class="text-right px-5 py-3">Líquido</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody>
                    <tr v-for="p in payrolls" :key="p.id" class="border-b border-white/5 hover:bg-white/5">
                        <td class="px-5 py-3 text-slate-100">{{ MONTHS[p.month - 1] }} {{ p.year }}</td>
                        <td class="px-5 py-3 text-right text-slate-200">{{ fmt(p.total_gross) }}</td>
                        <td class="px-5 py-3 text-right text-slate-400">{{ fmt(p.total_inss) }}</td>
                        <td class="px-5 py-3 text-right text-slate-400">{{ fmt(p.total_irt) }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-emerald-400">{{ fmt(p.total_net) }}</td>
                        <td class="px-5 py-3 text-right"><Link :href="`/app/salarios/${p.id}`" class="text-emerald-400 hover:underline">Recibos</Link></td>
                    </tr>
                    <tr v-if="payrolls.length === 0"><td colspan="6" class="px-5 py-12 text-center text-slate-500">Ainda não processou nenhuma folha.</td></tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<style scoped>
.lbl { display:block; font-size:.8rem; color:#cbd5e1; margin-bottom:.25rem; }
.field { padding:.45rem .65rem; background:#0e1320; border:1px solid rgba(255,255,255,.1); border-radius:.5rem; color:#e2e8f0; outline:none; }
.field:focus { border-color:#10b981; box-shadow:0 0 0 2px rgba(16,185,129,.3); }
</style>
