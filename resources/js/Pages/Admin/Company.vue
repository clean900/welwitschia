<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ company: Object, sms: Object, proxypay: Object });

const smsForm = useForm({
    api_key: '',
    sender_id: props.sms?.sender_id ?? '',
});

function saveSms() {
    smsForm.post(`/admin/empresas/${props.company.id}/sms`, { onSuccess: () => smsForm.reset('api_key') });
}
</script>

<template>
    <Head :title="company.name" />
    <AdminLayout>
        <Link href="/admin" class="text-sm text-slate-500 hover:text-slate-300">← Empresas</Link>
        <h1 class="text-2xl font-extrabold text-white mt-1 mb-1">{{ company.name }}</h1>
        <p class="text-slate-500 text-sm mb-6">
            <span class="font-mono">{{ company.id }}</span>
            <span v-if="company.nif"> · NIF {{ company.nif }}</span> · {{ company.status }}
        </p>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- ProxyPay (read-only — o cliente configura) -->
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="font-bold text-white">ProxyPay</h2>
                    <span :class="['text-xs font-semibold px-2.5 py-0.5 rounded-full', proxypay?.active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-600/30 text-slate-300']">
                        {{ proxypay?.active ? 'Activo' : 'Por configurar' }}
                    </span>
                </div>
                <p class="text-sm text-slate-400">Configurado pela própria empresa ({{ proxypay?.environment || '—' }}). O admin não acede à chave.</p>
            </div>

            <!-- SMS (activado pelo admin) -->
            <div class="bg-[#121829] border border-white/5 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="font-bold text-white">TelcoSMS</h2>
                    <span :class="['text-xs font-semibold px-2.5 py-0.5 rounded-full', sms?.active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-600/30 text-slate-300']">
                        {{ sms?.active ? 'Activo' : 'Por activar' }}
                    </span>
                </div>
                <p class="text-sm text-slate-400 mb-4">Activado pela plataforma. A empresa não vê a chave — só usa o serviço.</p>

                <form @submit.prevent="saveSms" class="space-y-3">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">API Key TelcoSMS</label>
                        <input v-model="smsForm.api_key" type="password" class="field" placeholder="••••••••" autocomplete="off" />
                        <p v-if="smsForm.errors.api_key" class="text-sm text-pink-400 mt-1">{{ smsForm.errors.api_key }}</p>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Sender ID</label>
                        <input v-model="smsForm.sender_id" type="text" maxlength="11" class="field" placeholder="EMPRESA" />
                        <p v-if="smsForm.errors.sender_id" class="text-sm text-pink-400 mt-1">{{ smsForm.errors.sender_id }}</p>
                    </div>
                    <button type="submit" :disabled="smsForm.processing" class="w-full py-2 rounded-lg bg-gradient-to-r from-pink-500 to-pink-600 text-white font-semibold hover:opacity-90 disabled:opacity-50">
                        {{ smsForm.processing ? 'A guardar…' : (sms?.active ? 'Reconfigurar SMS' : 'Activar SMS') }}
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.field { width:100%; padding:.5rem .7rem; background:#0e1320; border:1px solid rgba(255,255,255,.1); border-radius:.5rem; color:#e2e8f0; outline:none; }
.field:focus { border-color:#ec4899; box-shadow:0 0 0 2px rgba(236,72,153,.3); }
</style>
