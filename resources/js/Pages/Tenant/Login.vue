<script setup>
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    company: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head :title="`Entrar — ${company}`" />

    <div class="min-h-screen bg-stone-100 flex items-center justify-center px-6">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="text-xl font-extrabold">Welwitschia <span class="text-orange-700">ERP</span></div>
                <p class="text-stone-500 text-sm mt-1">{{ company }}</p>
            </div>

            <form @submit.prevent="submit" class="bg-white border border-stone-200 rounded-xl p-8 space-y-4">
                <h1 class="text-lg font-bold mb-2">Entrar</h1>

                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input v-model="form.email" type="email" class="field" autofocus />
                    <p v-if="form.errors.email" class="err">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Palavra-passe</label>
                    <input v-model="form.password" type="password" class="field" />
                    <p v-if="form.errors.password" class="err">{{ form.errors.password }}</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-stone-600">
                    <input v-model="form.remember" type="checkbox" /> Manter sessão iniciada
                </label>

                <button
                    type="submit" :disabled="form.processing"
                    class="w-full py-2 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800 disabled:opacity-50"
                >
                    {{ form.processing ? 'A entrar…' : 'Entrar' }}
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.field {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d6d3d1;
    border-radius: 0.375rem;
    outline: none;
}
.field:focus {
    border-color: #ea580c;
    box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.4);
}
.err {
    font-size: 0.875rem;
    color: #dc2626;
    margin-top: 0.25rem;
}
</style>
