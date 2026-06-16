<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ entries: Object });

const fmt = (v) => v ? new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz' : '—';
</script>

<template>
    <Head title="Razão" />
    <AppLayout>
        <h1 class="text-2xl font-extrabold text-white mb-2">Contabilidade</h1>
        <div class="flex gap-2 mb-6 text-sm">
            <Link href="/app/contabilidade" class="px-3 py-1.5 rounded-lg text-slate-400 hover:bg-white/5">Balancete</Link>
            <Link href="/app/contabilidade/razao" class="px-3 py-1.5 rounded-lg bg-emerald-500/15 text-emerald-300 font-medium">Razão</Link>
        </div>

        <div class="space-y-4">
            <div v-for="e in entries.data" :key="e.id" class="bg-[#121829] border border-white/5 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="text-white font-semibold">{{ e.description }}</div>
                        <div class="text-xs text-slate-500">{{ e.date }} <span v-if="e.reference">· {{ e.reference }}</span></div>
                    </div>
                    <div class="text-sm font-bold text-white">{{ fmt(e.total) }}</div>
                </div>
                <table class="w-full text-sm">
                    <tbody>
                        <tr v-for="(l, i) in e.lines" :key="i" class="border-t border-white/5">
                            <td class="py-1.5 font-mono text-slate-400 w-20">{{ l.account }}</td>
                            <td class="py-1.5 text-right text-emerald-400">{{ l.debit ? fmt(l.debit) : '' }}</td>
                            <td class="py-1.5 text-right text-pink-400">{{ l.credit ? fmt(l.credit) : '' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="entries.data.length === 0" class="bg-[#121829] border border-white/5 rounded-2xl p-12 text-center text-slate-500">
                Sem lançamentos contabilísticos ainda.
            </div>
        </div>

        <div v-if="entries.prev_page_url || entries.next_page_url" class="flex justify-between mt-4 text-sm">
            <button :disabled="!entries.prev_page_url" @click="router.get(entries.prev_page_url)" class="px-3 py-1.5 rounded-md border border-white/10 text-slate-300 disabled:opacity-40">Anterior</button>
            <button :disabled="!entries.next_page_url" @click="router.get(entries.next_page_url)" class="px-3 py-1.5 rounded-md border border-white/10 text-slate-300 disabled:opacity-40">Seguinte</button>
        </div>
    </AppLayout>
</template>
