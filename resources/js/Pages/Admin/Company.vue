<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ company: Object, sms: Object, proxypay: Object, agt: Object });

const smsForm = useForm({
    api_key: '',
    sender_id: props.sms?.sender_id ?? '',
});
const agtForm = useForm({
    tax_registration_number: props.agt?.tax_registration_number ?? '',
    establishment_number: props.agt?.establishment_number ?? '001',
    private_key: '',
});

function saveSms() {
    smsForm.post(`/admin/empresas/${props.company.id}/sms`, { onSuccess: () => smsForm.reset('api_key') });
}
function saveAgt() {
    agtForm.post(`/admin/empresas/${props.company.id}/agt`, { onSuccess: () => agtForm.reset('private_key') });
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

        <!-- AGT (Facturação Eletrónica) -->
        <div class="bg-[#121829] border border-white/5 rounded-2xl p-6 mt-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="font-bold text-white">AGT — Facturação Eletrónica</h2>
                <span :class="['text-xs font-semibold px-2.5 py-0.5 rounded-full', agt?.active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-600/30 text-slate-300']">
                    {{ agt?.active ? 'Activo' : 'Por configurar' }}
                </span>
            </div>
            <p class="text-sm text-slate-400 mb-4">NIF do emissor + chave privada RSA registada na AGT. As facturas emitidas são submetidas à AGT.</p>
            <form @submit.prevent="saveAgt" class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">NIF do emissor</label>
                    <input v-model="agtForm.tax_registration_number" type="text" class="field" placeholder="5000000000" />
                    <p v-if="agtForm.errors.tax_registration_number" class="text-sm text-pink-400 mt-1">{{ agtForm.errors.tax_registration_number }}</p>
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Estabelecimento</label>
                    <input v-model="agtForm.establishment_number" type="text" class="field" placeholder="001" />
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm text-slate-300 mb-1">Chave privada RSA do emissor (PEM)</label>
                    <textarea v-model="agtForm.private_key" rows="4" class="field font-mono text-xs" placeholder="-----BEGIN PRIVATE KEY-----"></textarea>
                    <p v-if="agtForm.errors.private_key" class="text-sm text-pink-400 mt-1">{{ agtForm.errors.private_key }}</p>
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button type="submit" :disabled="agtForm.processing" class="px-6 py-2 rounded-lg bg-gradient-to-r from-pink-500 to-pink-600 text-white font-semibold hover:opacity-90 disabled:opacity-50">
                        {{ agtForm.processing ? 'A guardar…' : (agt?.active ? 'Reconfigurar emissor' : 'Configurar emissor AGT') }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.field { width:100%; padding:.5rem .7rem; background:#0e1320; border:1px solid rgba(255,255,255,.1); border-radius:.5rem; color:#e2e8f0; outline:none; }
.field:focus { border-color:#ec4899; box-shadow:0 0 0 2px rgba(236,72,153,.3); }
</style>
