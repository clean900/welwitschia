<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    plans: Array,
});

const step = ref(1);

const form = useForm({
    company_name: '',
    slug: '',
    nif: '',
    plan: props.plans?.[0]?.slug ?? '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
    admin_password_confirmation: '',
});

// Gera o slug a partir do nome da empresa.
let slugTouched = false;
watch(() => form.company_name, (name) => {
    if (!slugTouched) {
        form.slug = name
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 63);
    }
});

const formatAOA = (v) => new Intl.NumberFormat('pt-PT').format(v) + ' Kz';

function next() {
    if (step.value < 3) step.value++;
}
function prev() {
    if (step.value > 1) step.value--;
}
function submit() {
    form.post('/registar-empresa');
}
</script>

<template>
    <Head title="Registar empresa — Welwitschia ERP" />

    <div class="min-h-screen bg-stone-100 text-stone-900">
        <header class="flex items-center justify-between px-6 py-4 border-b border-stone-200 bg-white">
            <Link href="/" class="text-xl font-extrabold tracking-tight">
                Welwitschia <span class="text-orange-700">ERP</span>
            </Link>
            <Link href="/" class="text-sm text-stone-500 hover:text-stone-800">Voltar</Link>
        </header>

        <div class="max-w-2xl mx-auto px-6 py-12">
            <!-- Steps -->
            <ol class="flex items-center justify-center gap-2 mb-10 text-xs font-mono">
                <li v-for="(label, i) in ['Empresa', 'Plano', 'Administrador']" :key="i"
                    class="flex items-center gap-2">
                    <span
                        :class="[
                            'w-7 h-7 rounded-full flex items-center justify-center font-bold',
                            step >= i + 1 ? 'bg-orange-700 text-white' : 'bg-stone-200 text-stone-500',
                        ]"
                    >{{ i + 1 }}</span>
                    <span :class="step >= i + 1 ? 'text-stone-900' : 'text-stone-400'">{{ label }}</span>
                    <span v-if="i < 2" class="w-8 h-px bg-stone-300"></span>
                </li>
            </ol>

            <div class="bg-white border border-stone-200 rounded-xl p-8">
                <!-- Passo 1 — Empresa -->
                <div v-show="step === 1" class="space-y-4">
                    <h2 class="text-lg font-bold mb-2">Dados da empresa</h2>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nome da empresa</label>
                        <input v-model="form.company_name" type="text" class="input" placeholder="Ex: Acme, Lda" />
                        <p v-if="form.errors.company_name" class="err">{{ form.errors.company_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Identificador (subdomínio)</label>
                        <div class="flex items-center">
                            <input
                                v-model="form.slug" @input="slugTouched = true" type="text"
                                class="input rounded-r-none" placeholder="acme"
                            />
                            <span class="px-3 py-2 bg-stone-100 border border-l-0 border-stone-300 rounded-r-md text-stone-500 text-sm">
                                .welwitschia.ao
                            </span>
                        </div>
                        <p v-if="form.errors.slug" class="err">{{ form.errors.slug }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">NIF (opcional)</label>
                        <input v-model="form.nif" type="text" class="input" placeholder="5000000000" />
                        <p v-if="form.errors.nif" class="err">{{ form.errors.nif }}</p>
                    </div>
                </div>

                <!-- Passo 2 — Plano -->
                <div v-show="step === 2" class="space-y-3">
                    <h2 class="text-lg font-bold mb-2">Escolha o plano</h2>
                    <label
                        v-for="plan in plans" :key="plan.slug"
                        :class="[
                            'block border rounded-lg p-4 cursor-pointer transition',
                            form.plan === plan.slug ? 'border-orange-600 bg-orange-50' : 'border-stone-200 hover:border-stone-300',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <input v-model="form.plan" :value="plan.slug" type="radio" class="mr-2" />
                                <span class="font-bold">{{ plan.name }}</span>
                                <span class="text-stone-500 text-sm"> · até {{ plan.max_users || '∞' }} utilizadores</span>
                            </div>
                            <div class="text-right">
                                <div class="font-bold">{{ plan.price_monthly > 0 ? formatAOA(plan.price_monthly) : 'Sob consulta' }}</div>
                                <div class="text-xs text-stone-400">/mês</div>
                            </div>
                        </div>
                        <p class="text-xs text-stone-500 mt-1 ml-6">{{ plan.description }}</p>
                    </label>
                    <p v-if="form.errors.plan" class="err">{{ form.errors.plan }}</p>
                </div>

                <!-- Passo 3 — Administrador -->
                <div v-show="step === 3" class="space-y-4">
                    <h2 class="text-lg font-bold mb-2">Conta de administrador</h2>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nome</label>
                        <input v-model="form.admin_name" type="text" class="input" />
                        <p v-if="form.errors.admin_name" class="err">{{ form.errors.admin_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input v-model="form.admin_email" type="email" class="input" />
                        <p v-if="form.errors.admin_email" class="err">{{ form.errors.admin_email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Palavra-passe</label>
                        <input v-model="form.admin_password" type="password" class="input" />
                        <p v-if="form.errors.admin_password" class="err">{{ form.errors.admin_password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Confirmar palavra-passe</label>
                        <input v-model="form.admin_password_confirmation" type="password" class="input" />
                    </div>
                </div>

                <!-- Navegação -->
                <div class="flex items-center justify-between mt-8">
                    <button v-if="step > 1" @click="prev" type="button"
                        class="px-4 py-2 rounded-md border border-stone-300 hover:bg-stone-50">
                        Anterior
                    </button>
                    <span v-else></span>

                    <button v-if="step < 3" @click="next" type="button"
                        class="px-6 py-2 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800">
                        Seguinte
                    </button>
                    <button v-else @click="submit" type="button" :disabled="form.processing"
                        class="px-6 py-2 rounded-md bg-orange-700 text-white font-semibold hover:bg-orange-800 disabled:opacity-50">
                        {{ form.processing ? 'A criar…' : 'Criar empresa' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d6d3d1;
    border-radius: 0.375rem;
    outline: none;
}
.input:focus {
    border-color: #ea580c;
    box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.4);
}
.err {
    font-size: 0.875rem;
    color: #dc2626;
    margin-top: 0.25rem;
}
</style>
