<script setup>
import { useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Card from 'primevue/card';

const props = defineProps({
    table: Object
});

const form = useForm({
    customer_name: '',
    customer_phone: ''
});

const submit = () => {
    form.post(route('customer.start', props.table.id));
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-white opacity-10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-white opacity-10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        
        <Card class="w-full max-w-md mx-4 backdrop-blur-md bg-white/80 dark:bg-gray-900/80 shadow-2xl border border-white/20 rounded-2xl overflow-hidden transform transition-all duration-300 hover:scale-[1.02]">
            <template #header>
                <div class="pt-8 pb-4 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white shadow-lg mb-4">
                        <i class="pi pi-receipt text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-800 dark:text-white mb-2 font-sans tracking-tight">Selamat Datang</h1>
                    <p class="text-gray-600 dark:text-gray-300 text-sm px-6">Anda berada di <span class="font-bold text-indigo-600 dark:text-indigo-400">Meja {{ table.number }}</span>. Silakan isi data diri Anda untuk mulai memesan.</p>
                </div>
            </template>
            <template #content>
                <form @submit.prevent="submit" class="px-2 pb-4 space-y-6">
                    <div class="space-y-2">
                        <label for="customer_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Lengkap</label>
                        <span class="p-input-icon-left w-full">
                            <i class="pi pi-user text-gray-400" />
                            <InputText 
                                id="customer_name" 
                                v-model="form.customer_name" 
                                class="w-full pl-10 p-3 bg-white/50 dark:bg-gray-800/50 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-indigo-500 rounded-xl transition-all" 
                                placeholder="Masukkan nama Anda"
                                required 
                                autofocus 
                            />
                        </span>
                        <small class="text-red-500" v-if="form.errors.customer_name">{{ form.errors.customer_name }}</small>
                    </div>

                    <div class="space-y-2">
                        <label for="customer_phone" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nomor WhatsApp</label>
                        <span class="p-input-icon-left w-full">
                            <i class="pi pi-phone text-gray-400" />
                            <InputText 
                                id="customer_phone" 
                                v-model="form.customer_phone" 
                                class="w-full pl-10 p-3 bg-white/50 dark:bg-gray-800/50 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-indigo-500 rounded-xl transition-all" 
                                placeholder="Contoh: 08123456789"
                                required 
                            />
                        </span>
                        <small class="text-red-500" v-if="form.errors.customer_phone">{{ form.errors.customer_phone }}</small>
                    </div>

                    <div class="pt-4">
                        <Button 
                            type="submit" 
                            :loading="form.processing" 
                            label="Lihat Menu & Pesan" 
                            icon="pi pi-arrow-right" 
                            iconPos="right" 
                            class="w-full p-4 text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-none rounded-xl shadow-lg hover:shadow-xl transition-all"
                        />
                    </div>
                </form>
            </template>
        </Card>
    </div>
</template>

<style scoped>
/* Optional specific micro-animations */
</style>
