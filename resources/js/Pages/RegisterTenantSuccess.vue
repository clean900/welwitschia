<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    company: String,
    subdomain: String,
});
</script>

<template>
    <Head title="Empresa criada — Welwitschia ERP" />

    <div class="min-h-screen bg-stone-100 text-stone-900 flex items-center justify-center px-6">
        <div class="bg-white border border-stone-200 rounded-2xl p-10 max-w-lg w-full text-center">
            <div class="w-14 h-14 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
                <span class="text-green-700 text-2xl">✓</span>
            </div>
            <h1 class="text-2xl font-extrabold mb-2">Empresa criada!</h1>
            <p class="text-stone-600 mb-6">
                A <strong>{{ company }}</strong> já tem o seu espaço isolado, plano de contas e administrador.
            </p>

            <div class="bg-stone-50 border border-stone-200 rounded-lg p-4 mb-6">
                <p class="text-xs text-stone-400 font-mono mb-1">O seu endereço de acesso</p>
                <p class="font-mono font-bold text-orange-700">{{ subdomain }}</p>
            </div>

            <p class="text-sm text-stone-500 mb-8">
                Entre com o email e a palavra-passe do administrador para configurar o ProxyPay e o SMS.
            </p>

            <Link href="/" class="inline-block px-6 py-3 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800">
                Voltar ao início
            </Link>
        </div>
    </div>
</template>
