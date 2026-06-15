<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    plansCount: Number,
});
</script>

<template>
    <Head title="Welwitschia ERP — Gestão empresarial angolana" />

    <div class="min-h-screen bg-stone-100 text-stone-900">
        <!-- Topbar -->
        <header class="flex items-center justify-between px-6 py-4 border-b border-stone-200 bg-white">
            <div class="text-xl font-extrabold tracking-tight">
                Welwitschia <span class="text-orange-700">ERP</span>
            </div>
            <nav class="flex items-center gap-3 text-sm">
                <Link v-if="canLogin" href="/login" class="px-4 py-2 rounded-md text-stone-600 hover:text-stone-900">
                    Entrar
                </Link>
                <Link
                    href="/registar-empresa"
                    class="px-4 py-2 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800"
                >
                    Registar empresa
                </Link>
            </nav>
        </header>

        <!-- Hero -->
        <section class="max-w-5xl mx-auto px-6 py-20 text-center">
            <p class="text-orange-700 font-mono text-xs uppercase tracking-widest mb-4">
                SaaS de gestão empresarial · Angola
            </p>
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-6">
                O ERP angolano que fecha o<br />
                <span class="text-orange-700">ciclo de cobrança</span> de ponta a ponta
            </h1>
            <p class="text-stone-600 max-w-2xl mx-auto mb-10">
                Facturação AGT, ProxyPay nativo, SMS por empresa, contabilidade PGC e salários
                com IRT/INSS — tudo multi-empresa, com os dados em Angola.
            </p>
            <div class="flex items-center justify-center gap-4">
                <Link
                    href="/registar-empresa"
                    class="px-6 py-3 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800"
                >
                    Começar agora
                </Link>
                <a href="#planos" class="px-6 py-3 rounded-md border border-stone-300 hover:bg-white">
                    Ver planos
                </a>
            </div>
        </section>

        <!-- Features -->
        <section class="max-w-5xl mx-auto px-6 pb-16 grid gap-5 md:grid-cols-3">
            <div class="bg-white border border-stone-200 rounded-xl p-6">
                <h3 class="font-bold mb-2">Cobrança automática</h3>
                <p class="text-sm text-stone-600">
                    Factura → referência ProxyPay → SMS ao cliente → reconciliação → lançamento contabilístico.
                </p>
            </div>
            <div class="bg-white border border-stone-200 rounded-xl p-6">
                <h3 class="font-bold mb-2">Contabilidade & RH</h3>
                <p class="text-sm text-stone-600">
                    PGC Angola em partidas dobradas e folha salarial com IRT e INSS calculados.
                </p>
            </div>
            <div class="bg-white border border-stone-200 rounded-xl p-6">
                <h3 class="font-bold mb-2">Multi-empresa isolado</h3>
                <p class="text-sm text-stone-600">
                    Cada empresa no seu próprio schema — isolamento total dos dados.
                </p>
            </div>
        </section>

        <!-- CTA planos -->
        <section id="planos" class="max-w-5xl mx-auto px-6 pb-24 text-center">
            <div class="bg-stone-900 text-white rounded-2xl p-10">
                <h2 class="text-2xl font-extrabold mb-3">{{ plansCount }} planos à medida da sua empresa</h2>
                <p class="text-stone-400 mb-6">Do Starter ao Unlimited. Pague só o que usa.</p>
                <Link
                    href="/registar-empresa"
                    class="inline-block px-6 py-3 rounded-md bg-orange-600 text-white font-semibold hover:bg-orange-700"
                >
                    Registar a minha empresa
                </Link>
            </div>
        </section>

        <footer class="text-center text-xs text-stone-400 py-8 font-mono">
            Welwitschia ERP · welwitschia.ao · dados alojados em Angola
        </footer>
    </div>
</template>
