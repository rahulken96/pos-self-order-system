<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Message from 'primevue/message';

const props = defineProps({
    table: Object,
    error: String,
});

const showResumeForm = ref(false);
const form = useForm({
    customer_phone: '',
});

const submitResume = () => {
    form.post(route('customer.resume', props.table.id));
};
</script>

<template>
    <Head title="Meja Sedang Terisi" />
    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col items-center justify-center font-sans px-6 relative overflow-hidden">
        <div class="absolute w-[500px] h-[500px] rounded-full bg-rose-500/10 blur-[100px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -z-10"></div>

        <div class="max-w-md w-full bg-slate-900/60 backdrop-blur-md border border-slate-800 p-8 rounded-3xl shadow-xl">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-rose-500/10 text-rose-500 mb-6">
                    <i class="pi pi-exclamation-triangle text-3xl"></i>
                </div>

                <h1 class="text-2xl font-black text-white mb-2">Meja Sedang Terisi</h1>
                <p class="text-rose-400 font-semibold mb-4">Meja {{ table.number }} sedang digunakan</p>
                
                <p class="text-slate-400 text-sm leading-relaxed mb-6">
                    Meja ini sedang digunakan. Jika Anda adalah pelanggan yang sedang duduk di meja ini dan ingin melanjutkan pesanan atau melihat tagihan, silakan lanjutkan dengan verifikasi nomor HP Anda.
                </p>
            </div>

            <!-- Phone Verification Form -->
            <div v-if="showResumeForm" class="mb-6 border-t border-slate-800 pt-6">
                <form @submit.prevent="submitResume" class="space-y-4 text-left">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-400">Verifikasi Nomor Handphone</label>
                        <InputText
                            v-model="form.customer_phone"
                            type="tel"
                            placeholder="Contoh: 08123456789"
                            class="w-full bg-slate-800 border-slate-700 text-white rounded-xl"
                            required
                        />
                        <p class="text-[10px] text-slate-500">Masukkan nomor HP yang sama saat Anda memulai pemesanan meja ini.</p>
                    </div>

                    <Message v-if="error" severity="error" size="small" class="text-xs">{{ error }}</Message>

                    <Button
                        type="submit"
                        label="Verifikasi & Masuk"
                        icon="pi pi-check"
                        class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 rounded-xl border-none shadow-md shadow-emerald-500/10 transition-colors"
                        :loading="form.processing"
                    />
                </form>
            </div>

            <div class="flex flex-col gap-3">
                <Button
                    v-if="!showResumeForm"
                    @click="showResumeForm = true"
                    label="Lanjutkan Sesi Saya di Meja Ini"
                    icon="pi pi-user"
                    class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 rounded-xl border-none shadow-md"
                />
                <Link
                    href="/"
                    class="w-full inline-flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-white font-bold py-3 px-6 rounded-xl border border-slate-700 transition-all text-sm"
                >
                    <i class="pi pi-home mr-2 text-xs"></i>
                    <span>Pilih Meja Lain</span>
                </Link>
            </div>
        </div>
    </div>
</template>
