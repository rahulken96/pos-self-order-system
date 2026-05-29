<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker'; // Wait, let's use standard native input type="date" to be extremely safe across PrimeVue version versions.
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    orders: Array,
    totalSales: Number,
    totalOrders: Number,
    topItems: Array,
    chartData: Array,
    paymentBreakdown: Array,
    filters: Object
});

const toast = useToast();
const fromDate = ref(props.filters.from || '');
const toDate = ref(props.filters.to || '');

const formatRp = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v);

const applyFilters = () => {
    router.get(route('admin.reports'), {
        from: fromDate.value,
        to: toDate.value
    }, { preserveState: true });
};

const exportExcel = () => {
    window.location.href = route('admin.reports.excel', {
        from: fromDate.value,
        to: toDate.value
    });
};

const exportPdf = () => {
    window.location.href = route('admin.reports.pdf', {
        from: fromDate.value,
        to: toDate.value
    });
};

// SVG Chart Calculations
const maxChartVal = computed(() => {
    const max = Math.max(...props.chartData.map(d => d.total));
    return max > 0 ? max : 100000;
});

const svgPoints = computed(() => {
    const data = props.chartData;
    const width = 500;
    const height = 150;
    const padding = 20;

    if (data.length === 0) return '';

    return data.map((d, index) => {
        const x = padding + (index * (width - 2 * padding) / (data.length - 1));
        const y = height - padding - (d.total * (height - 2 * padding) / maxChartVal.value);
        return `${x},${y}`;
    }).join(' ');
});

const svgAreaPoints = computed(() => {
    const points = svgPoints.value;
    if (!points) return '';
    const width = 500;
    const height = 150;
    const padding = 20;
    const startX = padding;
    const endX = padding + ((props.chartData.length - 1) * (width - 2 * padding) / (props.chartData.length - 1));
    const baseY = height - padding;

    return `${startX},${baseY} ${points} ${endX},${baseY}`;
});

const getMethodLabel = (method) => {
    return method === 'cash_to_kasir' ? 'Tunai' : 'Online (Xendit)';
};
</script>

<template>
    <Head title="Laporan Penjualan" />
    <AuthenticatedLayout>
        <div class="p-6">
            <Toast />

            <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-slate-800 mb-8">
                <div>
                    <h1 class="text-2xl font-extrabold">Laporan Penjualan & Analitik</h1>
                    <p class="text-xs text-slate-400">Analisis kinerja bisnis, omzet, dan export laporan</p>
                </div>
                <div class="flex gap-2">
                    <Button label="Excel Export" icon="pi pi-file-excel" class="bg-emerald-600 border-none font-bold" @click="exportExcel" />
                    <Button label="PDF Export" icon="pi pi-file-pdf" class="bg-red-650 border-none font-bold" @click="exportPdf" />
                </div>
            </header>

        <!-- Filters -->
        <div class="flex flex-col md:flex-row gap-4 mb-8 bg-slate-900 border border-slate-850 p-4 rounded-2xl items-end">
            <div class="flex-1 flex gap-4 w-full">
                <div class="flex-1 flex flex-col gap-1">
                    <label class="text-[10px] uppercase font-bold text-slate-400">Dari Tanggal</label>
                    <input type="date" v-model="fromDate" class="bg-slate-950 border border-slate-850 text-slate-100 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
                <div class="flex-1 flex flex-col gap-1">
                    <label class="text-[10px] uppercase font-bold text-slate-400">Sampai Tanggal</label>
                    <input type="date" v-model="toDate" class="bg-slate-950 border border-slate-850 text-slate-100 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
            </div>
            <Button label="Filter Data" icon="pi pi-filter" class="bg-indigo-650 border-none font-bold w-full md:w-36 h-10" @click="applyFilters" />
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-slate-900 border border-slate-850 rounded-2xl p-6 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Total Pendapatan</span>
                    <h3 class="text-2xl font-black text-emerald-400 mt-1">{{ formatRp(totalSales) }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-500/10 rounded-xl text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                    <i class="pi pi-wallet text-xl"></i>
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-850 rounded-2xl p-6 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Total Transaksi</span>
                    <h3 class="text-2xl font-black text-indigo-400 mt-1">{{ totalOrders }} Pesanan</h3>
                </div>
                <div class="w-12 h-12 bg-indigo-500/10 rounded-xl text-indigo-400 flex items-center justify-center border border-indigo-500/20">
                    <i class="pi pi-shopping-bag text-xl"></i>
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-850 rounded-2xl p-6 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Rata-rata Transaksi</span>
                    <h3 class="text-2xl font-black text-pink-400 mt-1">{{ formatRp(totalOrders > 0 ? totalSales / totalOrders : 0) }}</h3>
                </div>
                <div class="w-12 h-12 bg-pink-500/10 rounded-xl text-pink-400 flex items-center justify-center border border-pink-500/20">
                    <i class="pi pi-calculator text-xl"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- SVG Sales Trend Chart -->
            <div class="bg-slate-900 border border-slate-850 rounded-2xl p-5 lg:col-span-2 space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-base">Tren Penjualan (7 Hari Terakhir)</h2>
                        <p class="text-[10px] text-slate-400">Statistik omzet harian</p>
                    </div>
                </div>
                <!-- SVG Area Chart -->
                <div class="relative w-full overflow-hidden">
                    <svg viewBox="0 0 500 150" class="w-full h-auto">
                        <defs>
                            <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#4f46e5" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <!-- Grid Lines -->
                        <line x1="20" y1="20" x2="480" y2="20" stroke="#1e293b" stroke-dasharray="4" />
                        <line x1="20" y1="65" x2="480" y2="65" stroke="#1e293b" stroke-dasharray="4" />
                        <line x1="20" y1="110" x2="480" y2="110" stroke="#1e293b" stroke-dasharray="4" />
                        <line x1="20" y1="130" x2="480" y2="130" stroke="#334155" />

                        <!-- Area -->
                        <polygon :points="svgAreaPoints" fill="url(#chartGrad)" />
                        <!-- Line -->
                        <polyline :points="svgPoints" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                        <!-- Dots -->
                        <circle
                            v-for="(d, idx) in chartData"
                            :key="idx"
                            :cx="20 + (idx * 460 / (chartData.length - 1))"
                            :cy="130 - (d.total * 110 / maxChartVal)"
                            r="4"
                            fill="#6366f1"
                            stroke="#ffffff"
                            stroke-width="1"
                        />
                    </svg>
                </div>
                <!-- Labels -->
                <div class="flex justify-between px-4 text-[10px] text-slate-400">
                    <span v-for="d in chartData" :key="d.date">{{ d.date }}</span>
                </div>
            </div>

            <!-- Top Menu Items -->
            <div class="bg-slate-900 border border-slate-850 rounded-2xl p-5 space-y-4">
                <div>
                    <h2 class="font-bold text-base">5 Menu Terlaris</h2>
                    <p class="text-[10px] text-slate-400">Item yang paling sering dipesan</p>
                </div>
                <div class="space-y-3">
                    <div v-for="(item, idx) in topItems" :key="item.menu_item_id" class="flex justify-between items-center text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded bg-slate-800 text-[10px] font-bold text-slate-350 flex items-center justify-center">{{ idx + 1 }}</span>
                            <span class="font-semibold text-slate-200 truncate w-32">{{ item.menu_item?.name || 'Item Terhapus' }}</span>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-slate-100">{{ item.total_qty }} Qty</p>
                            <p class="text-[9px] text-slate-400">{{ formatRp(item.total_sales) }}</p>
                        </div>
                    </div>
                    <div v-if="topItems.length === 0" class="text-center py-6 text-xs text-slate-500">
                        Tidak ada data menu terlaris
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Breakdown -->
            <div class="bg-slate-900 border border-slate-850 rounded-2xl p-5 space-y-4">
                <div>
                    <h2 class="font-bold text-base"> Breakdown Metode Bayar</h2>
                    <p class="text-[10px] text-slate-400">Perbandingan tunai vs online</p>
                </div>
                <div class="space-y-4">
                    <div v-for="pay in paymentBreakdown" :key="pay.method" class="space-y-1.5">
                        <div class="flex justify-between text-xs font-semibold">
                            <span class="text-slate-400">{{ getMethodLabel(pay.method) }}</span>
                            <span>{{ formatRp(pay.total_sales) }} ({{ pay.count }} order)</span>
                        </div>
                        <div class="w-full bg-slate-950 h-2 rounded-full overflow-hidden">
                            <div class="bg-indigo-500 h-full" :style="{ width: `${totalSales > 0 ? (pay.total_sales / totalSales) * 100 : 0}%` }"></div>
                        </div>
                    </div>
                    <div v-if="paymentBreakdown.length === 0" class="text-center py-6 text-xs text-slate-500">
                        Tidak ada data transaksi pembayaran
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="bg-slate-900 border border-slate-850 rounded-2xl p-5 lg:col-span-2 space-y-4">
                <div>
                    <h2 class="font-bold text-base">Riwayat Transaksi Terkini</h2>
                    <p class="text-[10px] text-slate-400">Daftar transaksi selesai terbaru</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-slate-850 text-slate-400 font-bold">
                                <th class="pb-2">Order ID</th>
                                <th class="pb-2">Meja</th>
                                <th class="pb-2">Customer</th>
                                <th class="pb-2">Metode</th>
                                <th class="pb-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders.slice(0, 5)" :key="order.id" class="border-b border-slate-850/40 text-slate-200">
                                <td class="py-2.5">#{{ String(order.id).padStart(5, '0') }}</td>
                                <td class="py-2.5">Meja {{ order.table?.number }}</td>
                                <td class="py-2.5">{{ order.customer_name }}</td>
                                <td class="py-2.5">{{ order.payment ? (order.payment.method === 'cash_to_kasir' ? 'Tunai' : 'Online') : '-' }}</td>
                                <td class="py-2.5 text-right font-bold text-emerald-450">{{ formatRp(order.total_price) }}</td>
                            </tr>
                            <tr v-if="orders.length === 0">
                                <td colspan="5" class="text-center py-6 text-slate-500">Tidak ada transaksi ditemukan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </AuthenticatedLayout>
</template>
