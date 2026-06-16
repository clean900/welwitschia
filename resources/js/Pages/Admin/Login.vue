<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: false });

function submit() {
    form.post('/admin/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Back-office — Welwitschia" />

    <div class="min-h-screen bg-[#0a0e17] flex items-center justify-center px-6">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="text-xl font-extrabold text-white">Welwitschia <span class="text-pink-400">· Plataforma</span></div>
                <p class="text-slate-500 text-sm mt-1">Back-office (staff)</p>
            </div>

            <form @submit.prevent="submit" class="bg-[#121829] border border-white/5 rounded-xl p-8 space-y-4">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Email</label>
                    <input v-model="form.email" type="email" class="field" autofocus />
                    <p v-if="form.errors.email" class="text-sm text-pink-400 mt-1">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Palavra-passe</label>
                    <input v-model="form.password" type="password" class="field" />
                </div>
                <button type="submit" :disabled="form.processing" class="w-full py-2 rounded-lg bg-gradient-to-r from-pink-500 to-pink-600 text-white font-semibold hover:opacity-90 disabled:opacity-50">
                    {{ form.processing ? 'A entrar…' : 'Entrar' }}
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.field { width:100%; padding:.5rem .7rem; background:#0e1320; border:1px solid rgba(255,255,255,.1); border-radius:.5rem; color:#e2e8f0; outline:none; }
.field:focus { border-color:#ec4899; box-shadow:0 0 0 2px rgba(236,72,153,.3); }
</style>
