<script setup>
import Logo from '@/Components/Logo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: Boolean,
    plansCount: Number,
});

// Usa /img/hero.png (a planta) se existir; caso contrário cai no logo SVG.
const heroImageFailed = ref(false);

const nav = ['Soluções', 'Módulos', 'Integrações', 'Recursos', 'Preços', 'Sobre nós'];
const stats = [
    { value: 'AGT', label: 'Faturação certificada' },
    { value: 'ProxyPay', label: 'Pagamentos nativos' },
    { value: '100%', label: 'Conformidade PGC' },
    { value: 'Angola', label: 'Dados alojados cá' },
];
const cycle = ['Faturação', 'Cobrança', 'Conciliação', 'Contabilização'];
</script>

<template>
    <Head title="Welwitschia ERP — Gestão de Empresas (Angola)" />

    <div class="welw min-h-screen text-slate-200">
        <!-- NAV -->
        <header class="relative z-20 flex items-center justify-between px-8 py-5 max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <Logo :size="40" />
                <div class="leading-tight">
                    <div class="font-extrabold text-white tracking-tight">WELWITSCHIA</div>
                    <div class="text-[11px] font-bold text-emerald-400 tracking-[.2em]">ERP</div>
                </div>
            </div>
            <nav class="hidden lg:flex items-center gap-7 text-sm text-slate-300">
                <a v-for="n in nav" :key="n" href="#" class="hover:text-white transition">{{ n }}</a>
            </nav>
            <div class="flex items-center gap-3">
                <Link href="/login" class="px-5 py-2.5 rounded-xl border border-white/15 text-sm text-white hover:bg-white/5">Iniciar sessão</Link>
                <Link href="/registar-empresa" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-black bg-gradient-to-r from-emerald-400 to-emerald-500 hover:opacity-90 flex items-center gap-2">
                    Solicitar demonstração <span>→</span>
                </Link>
            </div>
        </header>

        <!-- HERO -->
        <section class="relative max-w-7xl mx-auto px-8 pt-10 pb-20 grid lg:grid-cols-2 gap-12 items-center">
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-300 text-xs font-mono mb-7">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    FEITO EM ANGOLA. PENSADO PARA O FUTURO.
                </div>
                <h1 class="text-5xl md:text-6xl font-extrabold leading-[1.05] text-white mb-6">
                    O ERP angolano<br />que fecha o<br />
                    <span class="bg-gradient-to-r from-emerald-400 to-pink-500 bg-clip-text text-transparent">ciclo de cobrança</span>
                </h1>
                <p class="text-slate-400 max-w-md mb-8">
                    Faturação AGT, ProxyPay nativo, contabilidade PGC e salários com IRT/INSS —
                    multi-empresa, com os dados em Angola.
                </p>
                <div class="flex flex-wrap gap-4 mb-12">
                    <Link href="/registar-empresa" class="px-6 py-3.5 rounded-xl font-semibold text-black bg-gradient-to-r from-emerald-400 to-emerald-500 hover:opacity-90 flex items-center gap-2">
                        Solicitar demonstração <span>→</span>
                    </Link>
                    <Link href="/login" class="px-6 py-3.5 rounded-xl font-semibold text-white border border-white/15 hover:bg-white/5 flex items-center gap-2">
                        ▶ Entrar
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <div v-for="s in stats" :key="s.label" class="border-l border-white/10 pl-3">
                        <div class="text-xl font-extrabold text-white">{{ s.value }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">{{ s.label }}</div>
                    </div>
                </div>
            </div>

            <!-- Visual (placeholder: substituir por /img/hero.png) -->
            <div class="relative h-[460px] hidden lg:block">
                <div class="hero-glow"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <img
                        v-show="!heroImageFailed"
                        src="/img/hero.png"
                        alt="Welwitschia"
                        class="max-h-[440px] w-auto object-contain drop-shadow-[0_0_60px_rgba(16,185,129,0.35)]"
                        @error="heroImageFailed = true"
                    />
                    <Logo v-if="heroImageFailed" :size="220" />
                </div>

                <!-- Cartão ciclo -->
                <div class="glass absolute left-0 top-6 w-52 p-4">
                    <div class="text-[10px] uppercase tracking-widest text-slate-400 mb-3">Ciclo de Cobrança</div>
                    <ul class="space-y-2">
                        <li v-for="c in cycle" :key="c" class="flex items-center gap-2 text-sm text-slate-200">
                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px]">✓</span>{{ c }}
                        </li>
                    </ul>
                </div>

                <!-- Cartão receitas -->
                <div class="glass absolute right-0 top-2 w-44 p-4">
                    <div class="text-[10px] uppercase tracking-widest text-slate-400">Receitas</div>
                    <div class="text-lg font-extrabold text-white mt-1">AOA 2.842.650</div>
                    <div class="text-[11px] text-emerald-400">▲ 28,5% vs mês anterior</div>
                    <svg viewBox="0 0 100 30" class="w-full h-8 mt-2" preserveAspectRatio="none">
                        <path d="M0 26 L20 22 L40 24 L60 14 L80 16 L100 4" fill="none" stroke="#34d399" stroke-width="2" />
                    </svg>
                </div>

                <!-- Cartão cobrança donut -->
                <div class="glass absolute right-4 bottom-6 w-40 p-4 text-center">
                    <div class="text-[10px] uppercase tracking-widest text-slate-400 mb-1">Cobrança</div>
                    <svg width="80" height="80" viewBox="0 0 80 80" class="mx-auto">
                        <circle cx="40" cy="40" r="32" fill="none" stroke="#1e293b" stroke-width="8" />
                        <circle cx="40" cy="40" r="32" fill="none" stroke="#34d399" stroke-width="8" stroke-linecap="round"
                            stroke-dasharray="185 201" transform="rotate(-90 40 40)" />
                        <text x="40" y="45" text-anchor="middle" class="fill-white" style="font-size:16px;font-weight:800">92%</text>
                    </svg>
                    <div class="text-[10px] text-slate-500">Taxa de sucesso</div>
                </div>
            </div>
        </section>

        <!-- MÓDULOS -->
        <section class="max-w-7xl mx-auto px-8 pb-20 grid md:grid-cols-3 gap-5">
            <div class="module module-green">
                <div class="badge-icon">AGT</div>
                <h3 class="text-emerald-400 text-xs font-bold tracking-widest mb-2">AGT BILLING</h3>
                <div class="text-white font-bold text-lg mb-2">Faturação inteligente.<br />Cobrança eficiente.</div>
                <p class="text-sm text-slate-400 mb-4">Automatize todo o ciclo de faturação e cobrança, com numeração certificada.</p>
                <a href="#" class="text-emerald-400 text-sm font-medium">Saber mais →</a>
            </div>
            <div class="module module-pink">
                <div class="badge-icon badge-pink">₽</div>
                <h3 class="text-pink-400 text-xs font-bold tracking-widest mb-2">PROXYPAY</h3>
                <div class="text-white font-bold text-lg mb-2">Pagamentos digitais.<br />Experiência sem fricção.</div>
                <p class="text-sm text-slate-400 mb-4">Integração nativa com ProxyPay para receber de forma rápida e automática.</p>
                <a href="#" class="text-pink-400 text-sm font-medium">Saber mais →</a>
            </div>
            <div class="module module-green">
                <div class="badge-icon">PGC</div>
                <h3 class="text-emerald-400 text-xs font-bold tracking-widest mb-2">PGC CONTABILIDADE</h3>
                <div class="text-white font-bold text-lg mb-2">Contabilidade que fala<br />a língua do seu negócio.</div>
                <p class="text-sm text-slate-400 mb-4">Totalmente alinhada ao Plano Geral de Contabilidade de Angola.</p>
                <a href="#" class="text-emerald-400 text-sm font-medium">Saber mais →</a>
            </div>
        </section>

        <!-- PARCEIROS (placeholder neutro) -->
        <section class="max-w-7xl mx-auto px-8 pb-16">
            <div class="text-center text-[11px] uppercase tracking-[.2em] text-slate-600 mb-6">Integra com o ecossistema angolano</div>
            <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-4 opacity-50">
                <span v-for="p in ['ProxyPay', 'TelcoSMS', 'AGT e-Fatura', 'INSS', 'Multicaixa']" :key="p" class="text-slate-400 font-semibold">{{ p }}</span>
            </div>
        </section>

        <footer class="text-center text-xs text-slate-600 py-10 border-t border-white/5">
            Welwitschia ERP · welwitschia.ao · {{ plansCount }} planos · dados alojados em Angola
        </footer>
    </div>
</template>

<style scoped>
.welw {
    background:
        radial-gradient(900px 500px at 75% 0%, rgba(16, 185, 129, 0.12), transparent 60%),
        radial-gradient(700px 500px at 90% 40%, rgba(236, 72, 153, 0.10), transparent 60%),
        #060a12;
}
.hero-glow {
    position: absolute;
    inset: 10% 15%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.35), rgba(236, 72, 153, 0.20) 45%, transparent 70%);
    filter: blur(40px);
}
.glass {
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 1rem;
    backdrop-filter: blur(8px);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
}
.module {
    border-radius: 1rem;
    padding: 1.5rem;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.06);
}
.module-green {
    box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.12);
}
.module-pink {
    box-shadow: inset 0 0 0 1px rgba(236, 72, 153, 0.15);
}
.badge-icon {
    width: 56px;
    height: 56px;
    border-radius: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: #34d399;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.3);
    margin-bottom: 1rem;
    box-shadow: 0 0 24px rgba(16, 185, 129, 0.25);
}
.badge-pink {
    color: #f472b6;
    background: rgba(236, 72, 153, 0.12);
    border-color: rgba(236, 72, 153, 0.3);
    box-shadow: 0 0 24px rgba(236, 72, 153, 0.25);
}
</style>
