<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    metrics: Object,
    revenue: Array,
    activity: Array,
    onboarding: Object,
});

const fmt = (v) => new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2 }).format(v || 0) + ' Kz';
const firstName = computed(() => usePage().props.auth?.user?.name?.split(' ')[0] ?? '');

// --- Gráfico de receitas (área SVG) ---
const W = 520, H = 140;
const maxRev = computed(() => Math.max(...props.revenue.map((r) => r.value), 1));
const pts = computed(() => props.revenue.map((r, i) => {
    const x = (i / (props.revenue.length - 1)) * W;
    const y = H - (r.value / maxRev.value) * (H - 20) - 5;
    return [x, y];
}));
const linePath = computed(() => pts.value.map((p, i) => (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1)).join(' '));
const areaPath = computed(() => `${linePath.value} L ${W} ${H} L 0 ${H} Z`);

// --- Donut (recebido vs por cobrar) ---
const donut = computed(() => {
    const r = props.metrics.received, o = props.metrics.outstanding;
    const total = r + o;
    const pct = total > 0 ? r / total : 0;
    const C = 2 * Math.PI * 52;
    return { pct: Math.round(pct * 100), dash: pct * C, gap: C - pct * C, C };
});

const eventLabels = {
    'invoice.issued': 'Fatura emitida',
    'invoice.paid': 'Fatura paga',
    'payment.reconciled': 'Pagamento reconciliado',
    'payment.transition': 'Pagamento atualizado',
    'payroll.processed': 'Folha processada',
    'tenant.provisioned': 'Empresa criada',
    'onboarding.proxypay_configured': 'ProxyPay configurado',
    'onboarding.sms_activated': 'SMS ativado',
    'sms.sent': 'SMS enviado',
    'accounting.entry_posted': 'Lançamento contabilístico',
};
const labelFor = (e) => eventLabels[e] ?? e;

const cards = computed(() => [
    { label: 'Faturado', value: props.metrics.invoiced, accent: 'emerald', sub: `${props.metrics.invoices_issued + props.metrics.invoices_paid} faturas` },
    { label: 'Recebido', value: props.metrics.received, accent: 'emerald', sub: `${props.metrics.invoices_paid} pagas` },
    { label: 'Por cobrar', value: props.metrics.outstanding, accent: 'pink', sub: `${props.metrics.invoices_issued} emitidas` },
    { label: 'Colaboradores', value: props.metrics.employees, accent: 'pink', sub: 'activos', plain: true },
]);
</script>

<template>
    <Head title="Painel" />
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-white">Bem-vindo, {{ firstName }} 👋</h1>
            <p class="text-slate-500 text-sm">Aqui está o resumo geral da sua empresa.</p>
        </div>

        <div v-if="!onboarding.proxypay || !onboarding.sms" class="mb-6 bg-amber-500/10 border border-amber-500/30 text-amber-300 rounded-xl p-4 text-sm">
            <strong>Conclua a configuração:</strong>
            <span v-if="!onboarding.proxypay"> falta o ProxyPay.</span>
            <span v-if="!onboarding.sms"> falta o SMS.</span>
        </div>

        <!-- KPI cards -->
        <div class="grid gap-4 md:grid-cols-4 mb-6">
            <div v-for="c in cards" :key="c.label" class="relative overflow-hidden bg-[#121829] border border-white/5 rounded-2xl p-5">
                <div class="text-xs text-slate-400 uppercase tracking-wide">{{ c.label }}</div>
                <div class="text-2xl font-extrabold text-white mt-1">{{ c.plain ? c.value : fmt(c.value) }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ c.sub }}</div>
                <svg v-if="!c.plain" class="absolute bottom-0 left-0 w-full h-10 opacity-60" viewBox="0 0 100 30" preserveAspectRatio="none">
                    <path :d="`M0 22 Q15 ${c.accent==='emerald'?8:18} 30 16 T60 12 T100 ${c.accent==='emerald'?6:20} V30 H0 Z`"
                        :fill="c.accent==='emerald' ? 'rgba(16,185,129,.18)' : 'rgba(236,72,153,.18)'" />
                </svg>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3 mb-6">
            <!-- Receitas -->
            <div class="lg:col-span-2 bg-[#121829] border border-white/5 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-white">Análise de Receitas</h2>
                    <span class="text-xs text-slate-500">Últimos 6 meses</span>
                </div>
                <svg :viewBox="`0 0 ${W} ${H}`" class="w-full" preserveAspectRatio="none" style="height:150px">
                    <defs>
                        <linearGradient id="rev" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="rgba(16,185,129,.45)" />
                            <stop offset="1" stop-color="rgba(16,185,129,0)" />
                        </linearGradient>
                    </defs>
                    <path :d="areaPath" fill="url(#rev)" />
                    <path :d="linePath" fill="none" stroke="#10b981" stroke-width="2.5" />
                </svg>
                <div class="flex justify-between mt-2 text-[11px] text-slate-500">
                    <span v-for="(m, i) in revenue" :key="i">{{ m.label }}</span>
                </div>
            </div>

            <!-- Visão financeira (donut) -->
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6">
                <h2 class="font-bold text-white mb-4">Visão Financeira</h2>
                <div class="flex flex-col items-center">
                    <svg width="140" height="140" viewBox="0 0 140 140">
                        <circle cx="70" cy="70" r="52" fill="none" stroke="#1e293b" stroke-width="14" />
                        <circle cx="70" cy="70" r="52" fill="none" stroke="#ec4899" stroke-width="14"
                            :stroke-dasharray="`${donut.C}`" transform="rotate(-90 70 70)" />
                        <circle cx="70" cy="70" r="52" fill="none" stroke="#10b981" stroke-width="14"
                            :stroke-dasharray="`${donut.dash} ${donut.gap}`" stroke-linecap="round" transform="rotate(-90 70 70)" />
                        <text x="70" y="66" text-anchor="middle" class="fill-white" style="font-size:20px;font-weight:800">{{ donut.pct }}%</text>
                        <text x="70" y="84" text-anchor="middle" fill="#64748b" style="font-size:10px">recebido</text>
                    </svg>
                    <div class="w-full mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-400"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Recebido</span>
                            <span class="text-white">{{ fmt(metrics.received) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-400"><span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span>Por cobrar</span>
                            <span class="text-white">{{ fmt(metrics.outstanding) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Atividade recente -->
        <div class="bg-[#121829] border border-white/5 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-white">Atividade Recente</h2>
                <Link href="/app/invoices" class="text-xs text-emerald-400 hover:underline">Ver faturas</Link>
            </div>
            <ul class="space-y-3">
                <li v-for="(a, i) in activity" :key="i" class="flex items-center gap-3 text-sm">
                    <span class="w-2 h-2 rounded-full bg-gradient-to-br from-emerald-500 to-pink-500"></span>
                    <span class="text-slate-200">{{ labelFor(a.event) }}</span>
                    <span class="ml-auto text-slate-500 text-xs">{{ a.at }}</span>
                </li>
                <li v-if="activity.length === 0" class="text-slate-500 text-sm">Sem atividade ainda.</li>
            </ul>
        </div>
    </AppLayout>
</template>
