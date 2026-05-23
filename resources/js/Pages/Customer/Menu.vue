<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Badge from 'primevue/badge';

const props = defineProps({
    table: Object,
    categories: Array,
    order: Object,
    estimated_wait_time: Number,
});

const toast = useToast();
const currentOrder = ref(props.order);
const searchQuery = ref('');

const selectedCategory = ref(props.categories.length > 0 ? props.categories[0].id : null);
const activeCategoryItems = computed(() => {
    const category = props.categories.find(c => c.id === selectedCategory.value);
    let items = category ? category.menu_items : [];
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        items = items.filter(item =>
            item.name.toLowerCase().includes(q) ||
            (item.description && item.description.toLowerCase().includes(q))
        );
    }
    return items;
});

// Search across ALL categories
const allSearchResults = computed(() => {
    if (!searchQuery.value) return [];
    const q = searchQuery.value.toLowerCase();
    let results = [];
    props.categories.forEach(cat => {
        cat.menu_items.forEach(item => {
            if (item.name.toLowerCase().includes(q) || (item.description && item.description.toLowerCase().includes(q))) {
                results.push(item);
            }
        });
    });
    return results;
});

const showSearchResults = computed(() => searchQuery.value.length > 0);
const displayItems = computed(() => showSearchResults.value ? allSearchResults.value : activeCategoryItems.value);

const cartTotal = computed(() => {
    return currentOrder.value?.total_price || 0;
});
const cartCount = computed(() => {
    return currentOrder.value?.items?.reduce((sum, item) => sum + item.quantity, 0) || 0;
});

// Modal state
const showItemModal = ref(false);
const showCartModal = ref(false);
const showPaymentModal = ref(false);
const activeItem = ref(null);
const quantity = ref(1);
const notes = ref('');
const isSubmitting = ref(false);

const openItemModal = (item) => {
    activeItem.value = item;
    quantity.value = 1;
    notes.value = '';
    showItemModal.value = true;
};

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
};

const addToCart = async () => {
    isSubmitting.value = true;
    try {
        const response = await axios.post(route('customer.addItem', currentOrder.value.id), {
            menu_item_id: activeItem.value.id,
            quantity: quantity.value,
            notes: notes.value
        });
        currentOrder.value = response.data;
        showItemModal.value = false;
        toast.add({ severity: 'success', summary: 'Berhasil', detail: `${activeItem.value.name} ditambahkan ke pesanan`, life: 3000 });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Terjadi kesalahan sistem.', life: 3000 });
    } finally {
        isSubmitting.value = false;
    }
};

const submitOrder = async () => {
    isSubmitting.value = true;
    try {
        await axios.post(route('customer.submitOrder', currentOrder.value.id));
        currentOrder.value.status = 'pending';
        showCartModal.value = false;
        toast.add({ severity: 'success', summary: 'Sukses', detail: 'Pesanan telah dikirim ke dapur!', life: 4000 });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengirim pesanan.', life: 3000 });
    } finally {
        isSubmitting.value = false;
    }
};

const requestBill = async () => {
    isSubmitting.value = true;
    try {
        await axios.patch(route('customer.requestBill', currentOrder.value.id));
        currentOrder.value.bill_requested = true;
        toast.add({ severity: 'info', summary: 'Info', detail: 'Permintaan bill dikirim ke Kasir.', life: 4000 });
        showPaymentModal.value = true;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal meminta bill.', life: 3000 });
    } finally {
        isSubmitting.value = false;
    }
};

const payOnline = async () => {
    isSubmitting.value = true;
    try {
        const response = await axios.post(route('customer.payOnline', currentOrder.value.id));
        window.location.href = response.data.payment_url;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menginisiasi pembayaran online.', life: 3000 });
        isSubmitting.value = false;
    }
};

// Real-time Echo listener
onMounted(() => {
    if (window.Echo) {
        window.Echo.channel('kitchen')
            .listen('OrderStatusUpdated', (e) => {
                if (e.order.id === currentOrder.value.id) {
                    currentOrder.value = e.order;

                    if (e.order.status === 'cooking') {
                        toast.add({ severity: 'info', summary: 'Status Pesanan', detail: 'Pesanan Anda mulai dimasak di dapur.', life: 5000 });
                    } else if (e.order.status === 'ready') {
                        toast.add({ severity: 'success', summary: 'Makanan Siap Saji', detail: 'Pesanan Anda siap disajikan! Silakan ambil.', life: 8000 });
                    } else if (e.order.status === 'completed') {
                        toast.add({ severity: 'success', summary: 'Pembayaran Lunas', detail: 'Terima kasih, pembayaran Anda berhasil dikonfirmasi!', life: 5000 });
                        setTimeout(() => { router.visit('/'); }, 3000);
                    } else if (e.order.status === 'cancelled') {
                        toast.add({ severity: 'error', summary: 'Pesanan Dibatalkan', detail: 'Pesanan Anda dibatalkan oleh kasir.', life: 5000 });
                        setTimeout(() => { router.visit('/'); }, 3000);
                    }
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leaveChannel('kitchen');
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-24 font-sans">
        <Toast />
        
        <!-- Header -->
        <div class="sticky top-0 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg shadow-sm border-b border-gray-200 dark:border-gray-800">
            <div class="px-4 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Meja {{ table.number }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Halo, {{ currentOrder?.customer_name }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-slate-400 py-1 px-2.5 rounded-full flex items-center gap-1 border border-slate-200/50 dark:border-gray-700">
                            <i class="pi pi-clock text-[10px]"></i>
                            <span>Antrean Dapur: ~{{ estimated_wait_time }} Menit</span>
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge v-if="currentOrder?.status" :value="currentOrder.status.toUpperCase()" :severity="currentOrder.status === 'draft' ? 'secondary' : currentOrder.status === 'ready' ? 'success' : currentOrder.status === 'cooking' ? 'warn' : 'info'" class="text-xs"></Badge>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="px-4 pb-2 pt-1 border-t border-gray-100 dark:border-gray-800">
                <span class="p-input-icon-left w-full">
                    <i class="pi pi-search text-gray-400" />
                    <InputText 
                        v-model="searchQuery" 
                        placeholder="Cari makanan atau minuman..." 
                        class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl"
                    />
                </span>
            </div>
            
            <!-- Category Tabs -->
            <div v-if="!showSearchResults" class="overflow-x-auto hide-scrollbar border-t border-gray-100 dark:border-gray-800">
                <div class="flex p-2 gap-2">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="selectedCategory = cat.id"
                        :class="[
                            'px-4 py-2 rounded-full whitespace-nowrap text-sm font-medium transition-all',
                            selectedCategory === cat.id 
                                ? 'bg-indigo-600 text-white shadow-md' 
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
                        ]"
                    >
                        {{ cat.name }}
                    </button>
                </div>
            </div>
            <div v-else class="px-4 py-2 border-t border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <i class="pi pi-search text-[10px]"></i> Hasil pencarian: {{ allSearchResults.length }} item ditemukan
                </p>
            </div>
        </div>

        <!-- Menu List -->
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
                v-for="item in displayItems" 
                :key="item.id"
                @click="openItemModal(item)"
                class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow border border-gray-100 dark:border-gray-700 flex gap-4 cursor-pointer"
            >
                <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-xl flex-shrink-0 overflow-hidden flex items-center justify-center text-gray-400">
                    <i v-if="!item.image" class="pi pi-image text-2xl"></i>
                    <img v-else :src="item.image" alt="Menu" class="w-full h-full object-cover" />
                </div>
                <div class="flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-white line-clamp-1">{{ item.name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">{{ item.description }}</p>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ formatRupiah(item.price) }}</span>
                        <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <i class="pi pi-plus text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="displayItems.length === 0" class="col-span-full py-12 text-center text-gray-400">
                <i class="pi pi-inbox text-4xl mb-3"></i>
                <p v-if="showSearchResults">Tidak ada menu yang cocok dengan pencarian.</p>
                <p v-else>Belum ada menu di kategori ini.</p>
            </div>
        </div>

        <!-- Floating Cart Button -->
        <div class="fixed bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-white via-white to-transparent dark:from-gray-900 dark:via-gray-900 z-30">
            <Button 
                v-if="currentOrder && (currentOrder.status === 'draft' || currentOrder.status === 'pending' || currentOrder.status === 'cooking' || currentOrder.status === 'ready')"
                @click="showCartModal = true" 
                class="w-full shadow-2xl p-4 rounded-2xl flex justify-between items-center bg-gray-900 dark:bg-indigo-600 border-none hover:bg-gray-800 dark:hover:bg-indigo-500 transition-colors"
            >
                <div class="flex items-center gap-3 text-white">
                    <div class="relative">
                        <i class="pi pi-shopping-bag text-xl"></i>
                        <span v-if="cartCount > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ cartCount }}</span>
                    </div>
                    <span class="font-bold">Lihat Pesanan</span>
                </div>
                <div class="text-white font-bold">{{ formatRupiah(cartTotal) }}</div>
            </Button>
            
            <!-- Pay Button (If Bill Requested) -->
            <Button 
                v-if="currentOrder?.bill_requested"
                @click="showPaymentModal = true" 
                severity="success"
                class="w-full shadow-2xl p-4 rounded-2xl flex justify-center items-center mt-2 font-bold transition-all"
                label="Pilih Metode Pembayaran"
                icon="pi pi-wallet"
            />
        </div>

        <!-- Add Item Modal -->
        <Dialog v-model:visible="showItemModal" :header="activeItem?.name" modal :style="{ width: '90vw', maxWidth: '400px' }" class="dark:bg-gray-800">
            <div class="pt-4 space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ activeItem?.description }}</p>
                <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900 p-4 rounded-xl">
                    <span class="font-semibold text-gray-700 dark:text-gray-200">Harga</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 text-lg">{{ activeItem ? formatRupiah(activeItem.price) : '' }}</span>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Jumlah Pesanan</label>
                    <div class="flex justify-center">
                        <InputNumber v-model="quantity" showButtons buttonLayout="horizontal" :min="1" :max="100" class="w-full"
                            decrementButtonClass="p-button-secondary" incrementButtonClass="p-button-secondary" incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan Khusus (Opsional)</label>
                    <Textarea v-model="notes" rows="3" class="w-full" placeholder="Contoh: Tidak pakai pedas, es dipisah..." />
                </div>
            </div>
            <template #footer>
                <Button label="Batal" icon="pi pi-times" text severity="secondary" @click="showItemModal = false" />
                <Button label="Tambah" icon="pi pi-check" :loading="isSubmitting" @click="addToCart" autofocus />
            </template>
        </Dialog>

        <!-- Cart Modal -->
        <Dialog v-model:visible="showCartModal" header="Pesanan Anda" modal :style="{ width: '95vw', maxWidth: '500px' }" class="dark:bg-gray-800">
            <div v-if="currentOrder?.items?.length === 0" class="text-center py-8 text-gray-500">
                <i class="pi pi-shopping-cart text-4xl mb-2"></i>
                <p>Pesanan masih kosong</p>
            </div>
            <div v-else class="space-y-4 pt-2 pb-24">
                <div v-for="item in currentOrder.items" :key="item.id" class="flex justify-between items-start border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 dark:text-white">{{ item.menu_item?.name }}</h4>
                        <p v-if="item.notes" class="text-xs text-orange-500 mt-1"><i class="pi pi-info-circle text-[10px]"></i> {{ item.notes }}</p>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ item.quantity }} x {{ formatRupiah(item.price) }}</div>
                    </div>
                    <div class="font-bold text-gray-800 dark:text-white text-right">
                        {{ formatRupiah(item.subtotal) }}
                    </div>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t-2 border-gray-200 dark:border-gray-600">
                    <span class="text-lg font-bold text-gray-800 dark:text-white">Total</span>
                    <span class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ formatRupiah(cartTotal) }}</span>
                </div>
            </div>
            
            <template #footer>
                <div class="w-full grid gap-2">
                    <Button 
                        v-if="currentOrder?.status === 'draft'"
                        label="Kirim Pesanan ke Dapur" 
                        icon="pi pi-send" 
                        :loading="isSubmitting" 
                        @click="submitOrder" 
                        class="w-full p-3 font-bold bg-indigo-600 border-none" 
                    />
                    <div v-else-if="!currentOrder?.bill_requested" class="w-full">
                        <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 p-3 rounded-lg text-sm mb-3 flex items-center gap-2">
                            <i class="pi pi-info-circle"></i> Pesanan sedang diproses. Anda bisa memesan lagi atau meminta tagihan.
                        </div>
                        <Button 
                            label="Minta Tagihan (Bill)" 
                            icon="pi pi-receipt" 
                            severity="warning"
                            :loading="isSubmitting" 
                            @click="requestBill" 
                            class="w-full p-3 font-bold shadow-md" 
                        />
                    </div>
                    <Button v-else label="Tutup" severity="secondary" text @click="showCartModal = false" class="w-full" />
                </div>
            </template>
        </Dialog>

        <!-- Payment Modal -->
        <Dialog v-model:visible="showPaymentModal" header="Metode Pembayaran" modal :style="{ width: '90vw', maxWidth: '400px' }" class="dark:bg-gray-800">
            <div class="pt-4 text-center space-y-4">
                <div class="w-20 h-20 bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="pi pi-check-circle text-4xl"></i>
                </div>
                <h3 class="font-bold text-xl text-gray-800 dark:text-white">Tagihan: {{ formatRupiah(cartTotal) }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">Silakan pilih metode pembayaran Anda.</p>
                
                <div class="space-y-3 pt-4">
                    <Button 
                        label="Bayar Online (QRIS/Transfer/E-Wallet)" 
                        icon="pi pi-credit-card" 
                        class="w-full p-4 font-bold bg-[#1A428A] border-none text-white shadow-lg hover:shadow-xl" 
                        :loading="isSubmitting"
                        @click="payOnline"
                    />
                    <div class="relative py-2">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500">ATAU</span>
                        </div>
                    </div>
                    <Button 
                        label="Bayar Tunai ke Kasir" 
                        icon="pi pi-money-bill" 
                        severity="secondary"
                        class="w-full p-4 font-bold" 
                        @click="showPaymentModal = false; showCartModal = false;"
                    />
                </div>
            </div>
        </Dialog>
    </div>
</template>

<style>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
