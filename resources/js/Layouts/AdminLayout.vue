<script setup>
import Logo from '@/Components/Logo.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const admin = computed(() => page.props.admin);
const flash = computed(() => page.props.flash ?? {});

function logout() {
    router.post('/admin/logout');
}
</script>

<template>
    <div class="min-h-screen bg-[#0a0e17] text-slate-200">
        <header class="bg-[#0e1320] border-b border-white/5">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Logo :size="32" />
                    <div class="leading-tight">
                        <div class="font-extrabold text-white">Welwitschia <span class="text-pink-400">· Plataforma</span></div>
                        <div class="text-[10px] uppercase tracking-widest text-slate-500">Back-office</div>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-slate-400">{{ admin?.name }}</span>
                    <button @click="logout" class="text-slate-500 hover:text-pink-400">Sair</button>
                </div>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 py-8">
            <div v-if="flash.success" class="mb-5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-lg px-4 py-3 text-sm">{{ flash.success }}</div>
            <slot />
        </main>
    </div>
</template>
