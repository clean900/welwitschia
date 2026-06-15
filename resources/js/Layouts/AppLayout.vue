<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const company = computed(() => page.props.company);
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash ?? {});
const current = computed(() => page.url);

function logout() {
    router.post('/logout');
}

const isActive = (prefix) => current.value === prefix || current.value.startsWith(prefix + '/') || (prefix === '/app' && current.value === '/app');
</script>

<template>
    <div class="min-h-screen bg-stone-100 text-stone-900">
        <header class="bg-white border-b border-stone-200">
            <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center gap-8">
                    <Link href="/app" class="leading-tight">
                        <div class="font-extrabold">Welwitschia <span class="text-orange-700">ERP</span></div>
                        <div class="text-xs text-stone-500">{{ company }}</div>
                    </Link>
                    <nav class="flex items-center gap-1 text-sm">
                        <Link
                            href="/app"
                            :class="['px-3 py-1.5 rounded-md', current === '/app' ? 'bg-orange-50 text-orange-700 font-semibold' : 'text-stone-600 hover:bg-stone-100']"
                        >Painel</Link>
                        <Link
                            href="/app/invoices"
                            :class="['px-3 py-1.5 rounded-md', isActive('/app/invoices') ? 'bg-orange-50 text-orange-700 font-semibold' : 'text-stone-600 hover:bg-stone-100']"
                        >Faturas</Link>
                    </nav>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-stone-600">{{ user?.name }}</span>
                    <button @click="logout" class="text-stone-500 hover:text-red-600">Sair</button>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx-auto px-6 py-8">
            <div v-if="flash.success" class="mb-5 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="mb-5 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                {{ flash.error }}
            </div>
            <slot />
        </main>
    </div>
</template>
