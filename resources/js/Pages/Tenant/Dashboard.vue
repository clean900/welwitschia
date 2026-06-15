<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    user: Object,
    metrics: Object,
    onboarding: Object,
});

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz';
const firstName = computed(() => usePage().props.auth?.user?.name?.split(' ')[0] ?? '');
</script>

<template>
    <Head title="Painel" />
    <AppLayout>
        <h1 class="text-2xl font-extrabold mb-6">Bom dia, {{ firstName }} 👋</h1>

        <div
            v-if="!onboarding.proxypay || !onboarding.sms"
            class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800"
        >
            <strong>Conclua a configuração:</strong>
            <span v-if="!onboarding.proxypay"> falta o ProxyPay.</span>
            <span v-if="!onboarding.sms"> falta o SMS.</span>
        </div>

        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <div class="bg-white border border-stone-200 rounded-xl p-5">
                <div class="text-xs text-stone-400 uppercase tracking-wide">Por cobrar</div>
                <div class="text-2xl font-extrabold mt-1">{{ fmt(metrics.outstanding) }}</div>
                <div class="text-xs text-stone-500 mt-1">{{ metrics.invoices_issued }} faturas emitidas</div>
            </div>
            <div class="bg-white border border-stone-200 rounded-xl p-5">
                <div class="text-xs text-stone-400 uppercase tracking-wide">Recebido</div>
                <div class="text-2xl font-extrabold mt-1 text-green-700">{{ fmt(metrics.revenue_paid) }}</div>
                <div class="text-xs text-stone-500 mt-1">{{ metrics.invoices_paid }} faturas pagas</div>
            </div>
            <div class="bg-white border border-stone-200 rounded-xl p-5">
                <div class="text-xs text-stone-400 uppercase tracking-wide">Colaboradores</div>
                <div class="text-2xl font-extrabold mt-1">{{ metrics.employees }}</div>
                <div class="text-xs text-stone-500 mt-1">activos na folha</div>
            </div>
        </div>

        <Link href="/app/invoices" class="inline-block px-5 py-2.5 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800">
            Ver faturas →
        </Link>
    </AppLayout>
</template>
