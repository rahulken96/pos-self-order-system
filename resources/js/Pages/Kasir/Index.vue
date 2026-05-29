<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Toast from 'primevue/toast';
import Badge from 'primevue/badge';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    tables: Array
});

const toast = useToast();
const activeTables = ref(props.tables.map(t => ({ ...t })));

// Selected table for payment modal
const selectedTable = ref(null);
const selectedOrder = computed(() => selectedTable.value?.orders?.[0] || null);
const showPaymentModal = ref(false);
const amountPaid = ref(0);
const isProcessing = ref(false);
const showReceiptLink = ref(false);
const lastCompletedOrderId = ref(null);

const formatRp = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v);
const change = computed(() => Math.max(0, (amountPaid.value || 0) - (selectedOrder.value?.total_price || 0)));

const openPayment = (table) => {
    selectedTable.value = table;
    amountPaid.value = selectedOrder.value?.total_price || 0;
    showReceiptLink.value = false;
    showPaymentModal.value = true;
};

const processPayment = async () => {
    if (!selectedOrder.value) return;
    isProcessing.value = true;
    try {
        const res = await axios.post(route('kasir.payment', selectedOrder.value.id), {
            amount: amountPaid.value
        });

        lastCompletedOrderId.value = selectedOrder.value.id;
        showReceiptLink.value = true;

        // Remove from active table locally
        const tIdx = activeTables.value.findIndex(t => t.id === selectedTable.value.id);
        if (tIdx !== -1) {
            activeTables.value[tIdx].orders = [];
            activeTables.value[tIdx].status = 'available';
        }

        toast.add({ severity: 'success', summary: 'Pembayaran Berhasil', detail: `Kembalian: ${formatRp(res.data.change)}`, life: 5000 });
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memproses pembayaran.', life: 3000 });
    } finally {
        isProcessing.value = false;
    }
};

const cancelOrder = async () => {
    if (!selectedOrder.value) return;
    if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini dan me-reset meja?')) return;
    
    isProcessing.value = true;
    try {
        const res = await axios.post(route('kasir.cancel', selectedOrder.value.id));
        
        const tIdx = activeTables.value.findIndex(t => t.id === selectedTable.value.id);
        if (tIdx !== -1) {
            activeTables.value[tIdx].orders = [];
            activeTables.value[tIdx].status = 'available';
        }
        
        showPaymentModal.value = false;
        toast.add({ severity: 'warn', summary: 'Pesanan Dibatalkan', detail: res.data.message, life: 5000 });
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal membatalkan pesanan.', life: 3000 });
    } finally {
        isProcessing.value = false;
    }
};

onMounted(() => {
    if (window.Echo) {
        window.Echo.channel('kasir')
            .listen('BillRequested', (e) => {
                // Mark the table that has requested bill
                const tIdx = activeTables.value.findIndex(t =>
                    t.orders?.some(o => o.id === e.order.id)
                );
                if (tIdx !== -1) {
                    const oIdx = activeTables.value[tIdx].orders.findIndex(o => o.id === e.order.id);
                    if (oIdx !== -1) {
                        activeTables.value[tIdx].orders[oIdx].bill_requested = true;
                    }
                }
                toast.add({
                    severity: 'warn',
                    summary: '🔔 Minta Bill!',
                    detail: `Meja ${e.order.table?.number} minta tagihan`,
                    life: 8000
                });
            })
            .listen('OrderStatusUpdated', (e) => {
                if (e.order.status === 'completed') {
                    // Remove completed orders from the table
                    activeTables.value.forEach((t, i) => {
                        activeTables.value[i].orders = t.orders.filter(o => o.id !== e.order.id);
                        if (activeTables.value[i].orders.length === 0) {
                            activeTables.value[i].status = 'available';
                        }
                    });
                } else {
                    // Update order status
                    activeTables.value.forEach((t, i) => {
                        const oIdx = t.orders.findIndex(o => o.id === e.order.id);
                        if (oIdx !== -1) activeTables.value[i].orders[oIdx] = e.order;
                    });
                }
            });
    }
});

onUnmounted(() => {
    if (window.Echo) window.Echo.leaveChannel('kasir');
});

const getTableStatus = (table) => {
    const order = table.orders?.[0];
    if (!order) return { label: 'Tersedia', color: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400', dot: 'bg-emerald-500' };
    if (order.bill_requested) return { label: 'Minta Bill', color: 'bg-amber-500/10 border-amber-500/30 text-amber-400 ring-1 ring-amber-400/30', dot: 'bg-amber-500 animate-ping' };
    if (order.status === 'ready') return { label: 'Siap Saji', color: 'bg-sky-500/10 border-sky-500/20 text-sky-400', dot: 'bg-sky-500' };
    if (order.status === 'cooking') return { label: 'Dimasak', color: 'bg-orange-500/10 border-orange-500/20 text-orange-400', dot: 'bg-orange-500' };
    if (order.status === 'pending') return { label: 'Menunggu', color: 'bg-red-500/10 border-red-500/20 text-red-400', dot: 'bg-red-500 animate-ping' };
    return { label: 'Diisi', color: 'bg-purple-500/10 border-purple-500/20 text-purple-400', dot: 'bg-purple-500' };
};
</script>

<template>
    <Head title="Dashboard Kasir" />
    <AuthenticatedLayout>
        <div class="p-6">
            <Toast />

        <!-- Header -->
        <header class="flex justify-between items-center pb-6 border-b border-slate-800 mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center border border-indigo-500/30">
                    <i class="pi pi-desktop text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold">Dashboard Kasir</h1>
                    <p class="text-xs text-slate-400">Monitor & proses pembayaran semua meja</p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-ping"></span>
                <span class="text-sm font-semibold text-green-400">Live</span>
            </div>
        </header>

        <!-- Legend -->
        <div class="flex flex-wrap gap-3 mb-8">
            <div v-for="leg in [
                { label: 'Tersedia', dot: 'bg-emerald-500' },
                { label: 'Menunggu Dapur', dot: 'bg-red-500' },
                { label: 'Dimasak', dot: 'bg-orange-500' },
                { label: 'Siap Saji', dot: 'bg-sky-500' },
                { label: 'Minta Bill', dot: 'bg-amber-500' },
            ]" :key="leg.label" class="flex items-center gap-2 text-xs text-slate-400">
                <span :class="['w-2.5 h-2.5 rounded-full', leg.dot]"></span>
                {{ leg.label }}
            </div>
        </div>

        <!-- Table Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <div
                v-for="table in activeTables"
                :key="table.id"
                @click="table.orders?.length ? openPayment(table) : null"
                :class="[
                    'rounded-2xl border p-5 flex flex-col justify-between transition-all duration-200 select-none',
                    getTableStatus(table).color,
                    table.orders?.length ? 'cursor-pointer hover:scale-105' : 'cursor-default opacity-70'
                ]"
                :style="{ minHeight: '130px' }"
            >
                <div class="flex justify-between items-start">
                    <span class="text-3xl font-black">{{ table.number }}</span>
                    <div class="flex items-center gap-1.5">
                        <span :class="['w-2.5 h-2.5 rounded-full', getTableStatus(table).dot]"></span>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-bold mt-2">{{ getTableStatus(table).label }}</p>
                    <p v-if="table.orders?.[0]?.customer_name" class="text-[10px] opacity-70 truncate">{{ table.orders[0].customer_name }}</p>
                    <p v-if="table.orders?.[0]?.total_price" class="text-xs font-bold mt-1">{{ formatRp(table.orders[0].total_price) }}</p>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <Dialog v-model:visible="showPaymentModal" :header="`Meja ${selectedTable?.number} — Proses Pembayaran`" modal :style="{ width: '95vw', maxWidth: '480px' }">
            <div v-if="!showReceiptLink && selectedOrder" class="pt-4 space-y-6">
                <!-- Order Items -->
                <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-4 space-y-3 max-h-48 overflow-y-auto">
                    <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between text-sm">
                        <span class="text-slate-700 dark:text-slate-200">{{ item.quantity }}x {{ item.menu_item?.name }}</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-100">{{ formatRp(item.subtotal) }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center text-base border-t dark:border-slate-700 pt-4">
                    <span class="font-bold text-slate-800 dark:text-slate-100">Total Tagihan</span>
                    <span class="font-black text-indigo-600 dark:text-indigo-400 text-xl">{{ formatRp(selectedOrder.total_price) }}</span>
                </div>

                <!-- Cash amount input -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Uang Diterima (Rp)</label>
                    <InputNumber v-model="amountPaid" :min="selectedOrder.total_price" :step="1000" mode="currency" currency="IDR" locale="id-ID" class="w-full" />
                </div>

                <div class="flex justify-between items-center bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-500/30 rounded-xl p-4">
                    <span class="font-bold text-emerald-700 dark:text-emerald-400">Kembalian</span>
                    <span class="font-black text-emerald-700 dark:text-emerald-400 text-xl">{{ formatRp(change) }}</span>
                </div>
            </div>

            <!-- Receipt Link after success -->
            <div v-if="showReceiptLink" class="py-8 text-center space-y-4">
                <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center mx-auto">
                    <i class="pi pi-check-circle text-4xl text-emerald-500"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Pembayaran Berhasil!</h3>
                <a :href="route('kasir.receipt', lastCompletedOrderId)" target="_blank">
                    <Button label="Cetak Struk PDF" icon="pi pi-print" class="mt-2 font-bold" />
                </a>
            </div>

            <template #footer>
                <div v-if="!showReceiptLink" class="flex gap-2 w-full">
                    <Button label="Tutup" severity="secondary" text @click="showPaymentModal = false" class="flex-1" />
                    <Button label="Batalkan Pesanan" severity="danger" outlined :loading="isProcessing" @click="cancelOrder" class="flex-1" />
                    <Button label="Konfirmasi Bayar" icon="pi pi-check" :loading="isProcessing" @click="processPayment" class="flex-1 font-bold bg-indigo-600 border-none" />
                </div>
                <Button v-else label="Tutup" severity="secondary" text @click="showPaymentModal = false" class="w-full" />
            </template>
        </Dialog>
        </div>
    </AuthenticatedLayout>
</template>
