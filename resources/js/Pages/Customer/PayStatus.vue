<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { computed } from 'vue';

const props = defineProps({
    order: Object
});

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const paymentStatus = computed(() => {
    if (props.order.status === 'completed') {
        return {
            title: 'Pembayaran Berhasil',
            message: 'Terima kasih, pembayaran Anda telah kami terima.',
            icon: 'pi pi-check-circle',
            color: 'text-green-500 bg-green-100 dark:bg-green-900/30'
        };
    } else {
        return {
            title: 'Menunggu Pembayaran',
            message: 'Pembayaran Anda sedang kami proses atau masih tertunda di pihak pembayaran.',
            icon: 'pi pi-clock',
            color: 'text-yellow-500 bg-yellow-100 dark:bg-yellow-900/30'
        };
    }
});
</script>

<template>
    <Head title="Status Pembayaran" />

    <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 p-4 font-sans relative overflow-hidden">
        <!-- Abstract Background -->
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-indigo-500/10 to-transparent"></div>
        
        <Card class="w-full max-w-md shadow-xl border border-gray-100 dark:border-gray-800 rounded-2xl relative z-10 overflow-hidden backdrop-blur-md bg-white/90 dark:bg-gray-800/90">
            <template #content>
                <div class="text-center py-8 px-4">
                    <div :class="['w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm', paymentStatus.color]">
                        <i :class="[paymentStatus.icon, 'text-5xl']"></i>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">{{ paymentStatus.title }}</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-8">{{ paymentStatus.message }}</p>
                    
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 mb-8 text-left space-y-3 border border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Order ID</span>
                            <span class="font-bold text-gray-800 dark:text-white">#{{ order.id }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Nama</span>
                            <span class="font-bold text-gray-800 dark:text-white">{{ order.customer_name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Meja</span>
                            <span class="font-bold text-gray-800 dark:text-white">{{ order.table.number }}</span>
                        </div>
                        <div class="flex justify-between text-sm border-t border-gray-200 dark:border-gray-700 pt-3 mt-1">
                            <span class="font-bold text-gray-800 dark:text-white">Total Tagihan</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 text-lg">{{ formatRupiah(order.total_price) }}</span>
                        </div>
                    </div>

                    <Link :href="route('customer.order', order.table_id)">
                        <Button label="Kembali ke Pesanan" icon="pi pi-arrow-left" class="w-full p-4 font-bold rounded-xl" />
                    </Link>
                </div>
            </template>
        </Card>
    </div>
</template>
