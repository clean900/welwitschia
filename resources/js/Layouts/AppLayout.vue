<script setup>
import Logo from '@/Components/Logo.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const company = computed(() => page.props.company);
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash ?? {});
const url = computed(() => page.url);

function logout() {
    router.post('/logout');
}

const nav = [
    { label: 'Painel', href: '/app', icon: 'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z', enabled: true },
    { label: 'Faturas', href: '/app/invoices', icon: 'M7 3h8l4 4v14H7zM14 3v5h5M10 13h6M10 17h6', enabled: true },
    { label: 'Clientes', href: '/app/clientes', icon: 'M16 14a4 4 0 10-8 0M4 21a6 6 0 0112 0M18 11a3 3 0 10-2-5', enabled: true },
    { label: 'Cobranças', href: '/app/cobrancas', icon: 'M2 7h20v10H2zM12 9a3 3 0 100 6 3 3 0 000-6', enabled: true },
    { label: 'Contabilidade', href: '/app/contabilidade', icon: 'M5 4h12a2 2 0 012 2v14H7a2 2 0 01-2-2zM9 8h6M9 12h6', enabled: true },
    { label: 'RH & Salários', href: '/app/colaboradores', icon: 'M16 14a4 4 0 10-8 0M4 21a6 6 0 0112 0M18 11a3 3 0 10-2-5', enabled: true },
    { label: 'Relatórios', icon: 'M4 20V10M10 20V4M16 20v-7M21 20H3', enabled: false },
    { label: 'Definições', href: '/app/onboarding', icon: 'M12 15a3 3 0 100-6 3 3 0 000 6zM19 12a7 7 0 00-.1-1l2-1.5-2-3.4-2.3 1a7 7 0 00-1.7-1l-.3-2.5H9.4l-.3 2.5a7 7 0 00-1.7 1l-2.3-1-2 3.4L5 11a7 7 0 000 2l-2 1.5 2 3.4 2.3-1a7 7 0 001.7 1l.3 2.5h5.2l.3-2.5a7 7 0 001.7-1l2.3 1 2-3.4-2-1.5a7 7 0 00.1-1z', enabled: true },
];

const isActive = (href) => href === '/app' ? url.value === '/app' : url.value.startsWith(href);
</script>

<template>
    <div class="flex min-h-screen bg-[#0a0e17] text-slate-200">
        <!-- Sidebar -->
        <aside class="w-64 shrink-0 bg-[#0e1320] border-r border-white/5 flex flex-col">
            <div class="px-5 py-5 flex items-center gap-3 border-b border-white/5">
                <Logo :size="34" />
                <div class="leading-tight">
                    <div class="font-extrabold text-white">Welwitschia</div>
                    <div class="text-[10px] uppercase tracking-widest text-emerald-400/80">Gestão de Empresas</div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1">
                <template v-for="item in nav" :key="item.label">
                    <Link
                        v-if="item.enabled"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition',
                            isActive(item.href)
                                ? 'bg-gradient-to-r from-emerald-500/20 to-pink-500/10 text-white border border-emerald-500/30'
                                : 'text-slate-400 hover:bg-white/5 hover:text-slate-200',
                        ]"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path :d="item.icon" /></svg>
                        {{ item.label }}
                    </Link>
                    <div v-else class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600 cursor-default" title="Em breve">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path :d="item.icon" /></svg>
                        {{ item.label }}
                        <span class="ml-auto text-[9px] uppercase tracking-wide text-slate-700">brevemente</span>
                    </div>
                </template>
            </nav>

            <div class="px-4 py-4 border-t border-white/5 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-500 to-pink-500 flex items-center justify-center text-white font-bold text-sm">
                    {{ (user?.name || '?').charAt(0) }}
                </div>
                <div class="flex-1 leading-tight min-w-0">
                    <div class="text-sm text-white truncate">{{ user?.name }}</div>
                    <div class="text-[11px] text-slate-500">Administrador</div>
                </div>
                <button @click="logout" title="Sair" class="text-slate-500 hover:text-pink-400">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" /></svg>
                </button>
            </div>
        </aside>

        <!-- Conteúdo -->
        <div class="flex-1 min-w-0 flex flex-col">
            <header class="h-16 border-b border-white/5 flex items-center gap-4 px-8">
                <div class="flex-1 max-w-md">
                    <div class="flex items-center gap-2 bg-white/5 rounded-lg px-3 py-2 text-sm text-slate-500">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4-4" /></svg>
                        Procurar…
                    </div>
                </div>
                <div class="text-sm text-slate-400">{{ company }}</div>
            </header>

            <main class="flex-1 p-8">
                <div v-if="flash.success" class="mb-5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-lg px-4 py-3 text-sm">{{ flash.success }}</div>
                <div v-if="flash.error" class="mb-5 bg-pink-500/10 border border-pink-500/30 text-pink-300 rounded-lg px-4 py-3 text-sm">{{ flash.error }}</div>
                <slot />
            </main>
        </div>
    </div>
</template>
