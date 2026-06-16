<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: Object,
    proxypay: Object,
    sms: Object,
});

const ppForm = useForm({
    api_key: '',
    environment: props.proxypay?.environment ?? 'sandbox',
    webhook_secret: '',
});
const smsForm = useForm({
    api_key: '',
    sender_id: props.sms?.sender_id ?? '',
});

function saveProxyPay() {
    ppForm.post('/app/onboarding/proxypay', { onSuccess: () => ppForm.reset('api_key', 'webhook_secret') });
}
function saveSms() {
    smsForm.post('/app/onboarding/sms', { onSuccess: () => smsForm.reset('api_key') });
}
</script>

<template>
    <Head title="Configuração" />
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-white">Configuração</h1>
            <p class="text-slate-500 text-sm">Active os serviços de pagamento e SMS da sua empresa.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- ProxyPay -->
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="font-bold text-white">ProxyPay</h2>
                    <span :class="['text-xs font-semibold px-2.5 py-0.5 rounded-full', status.proxypay ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-600/30 text-slate-300']">
                        {{ status.proxypay ? 'Activo' : 'Por configurar' }}
                    </span>
                </div>
                <p class="text-sm text-slate-400 mb-4">A sua API Key ProxyPay. Os pagamentos vão diretamente para a sua conta bancária — nunca passam pela Welwitschia.</p>

                <form @submit.prevent="saveProxyPay" class="space-y-3">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">API Key</label>
                        <input v-model="ppForm.api_key" type="password" class="field" placeholder="••••••••" autocomplete="off" />
                        <p v-if="ppForm.errors.api_key" class="text-sm text-pink-400 mt-1">{{ ppForm.errors.api_key }}</p>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Ambiente</label>
                        <select v-model="ppForm.environment" class="field">
                            <option value="sandbox">Sandbox (testes)</option>
                            <option value="production">Produção</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Webhook secret (opcional)</label>
                        <input v-model="ppForm.webhook_secret" type="password" class="field" placeholder="HMAC dos callbacks" autocomplete="off" />
                    </div>
                    <button type="submit" :disabled="ppForm.processing" class="w-full py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90 disabled:opacity-50">
                        {{ ppForm.processing ? 'A guardar…' : (status.proxypay ? 'Reconfigurar ProxyPay' : 'Activar ProxyPay') }}
                    </button>
                </form>
            </div>

            <!-- SMS -->
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="font-bold text-white">Serviço de SMS</h2>
                    <span :class="['text-xs font-semibold px-2.5 py-0.5 rounded-full', status.sms ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-600/30 text-slate-300']">
                        {{ status.sms ? 'Activo' : 'Por configurar' }}
                    </span>
                </div>
                <p class="text-sm text-slate-400 mb-4">Os SMS de cobrança saem com o nome da sua empresa (Sender ID).</p>

                <form @submit.prevent="saveSms" class="space-y-3">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">API Key</label>
                        <input v-model="smsForm.api_key" type="password" class="field" placeholder="••••••••" autocomplete="off" />
                        <p v-if="smsForm.errors.api_key" class="text-sm text-pink-400 mt-1">{{ smsForm.errors.api_key }}</p>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Sender ID (máx. 11 caracteres)</label>
                        <input v-model="smsForm.sender_id" type="text" maxlength="11" class="field" placeholder="ACME" />
                        <p v-if="smsForm.errors.sender_id" class="text-sm text-pink-400 mt-1">{{ smsForm.errors.sender_id }}</p>
                    </div>
                    <button type="submit" :disabled="smsForm.processing" class="w-full py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold hover:opacity-90 disabled:opacity-50">
                        {{ smsForm.processing ? 'A guardar…' : (status.sms ? 'Reconfigurar SMS' : 'Activar SMS') }}
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.field {
    width: 100%;
    padding: 0.5rem 0.7rem;
    background: #0e1320;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    color: #e2e8f0;
    outline: none;
}
.field:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
}
</style>
