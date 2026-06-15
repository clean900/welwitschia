<script setup>
import { Head, router } from '@inertiajs/vue3';

defineProps({
    company: String,
    user: Object,
    metrics: Object,
    onboarding: Object,
});

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v) + ' Kz';

function logout() {
    router.post('/logout');
}
</script>

<template>
    <Head :title="`Painel — ${company}`" />

    <div class="min-h-screen bg-stone-100 text-stone-900">
        <header class="flex items-center justify-between px-6 py-4 border-b border-stone-200 bg-white">
            <div>
                <div class="font-extrabold">Welwitschia <span class="text-orange-700">ERP</span></div>
                <div class="text-xs text-stone-500">{{ company }}</div>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span class="text-stone-600">{{ user.name }}</span>
                <button @click="logout" class="text-stone-500 hover:text-red-600">Sair</button>
            </div>
        </header>

        <main class="max-w-5xl mx-auto px-6 py-8">
            <h1 class="text-2xl font-extrabold mb-6">Bom dia, {{ user.name.split(' ')[0] }} 👋</h1>

            <!-- Onboarding banner -->
            <div
                v-if="!onboarding.proxypay || !onboarding.sms"
                class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800"
            >
                <strong>Conclua a configuração:</strong>
                <span v-if="!onboarding.proxypay"> falta o ProxyPay.</span>
                <span v-if="!onboarding.sms"> falta o SMS.</span>
            </div>

            <!-- Metrics -->
            <div class="grid gap-4 md:grid-cols-3 mb-6">
                <div class="bg-white border border-stone-200 rounded-xl p-5">
                    <div class="text-xs text-stone-400 uppercase tracking-wide">Por cobrar</div>
                    <div class="text-2xl font-extrabold mt-1">{{ fmt(metrics.outstanding) }}</div>
                    <div class="text-xs text-stone-500 mt-1">{{ metrics.invoices_issued }} facturas emitidas</div>
                </div>
                <div class="bg-white border border-stone-200 rounded-xl p-5">
                    <div class="text-xs text-stone-400 uppercase tracking-wide">Recebido</div>
                    <div class="text-2xl font-extrabold mt-1 text-green-700">{{ fmt(metrics.revenue_paid) }}</div>
                    <div class="text-xs text-stone-500 mt-1">{{ metrics.invoices_paid }} facturas pagas</div>
                </div>
                <div class="bg-white border border-stone-200 rounded-xl p-5">
                    <div class="text-xs text-stone-400 uppercase tracking-wide">Colaboradores</div>
                    <div class="text-2xl font-extrabold mt-1">{{ metrics.employees }}</div>
                    <div class="text-xs text-stone-500 mt-1">activos na folha</div>
                </div>
            </div>

            <div class="bg-white border border-stone-200 rounded-xl p-6 text-sm text-stone-500">
                Facturação, contabilidade e salários estão disponíveis via API.
                Os ecrãs completos chegam nas próximas iterações.
            </div>
        </main>
    </div>
</template>
