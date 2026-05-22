<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Badge from 'primevue/badge';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    orders: Array
});

const toast = useToast();
const activeOrders = ref([...props.orders]);

// Web Audio API helper to play chime sound on new order
const playChime = () => {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        // First note (G5)
        const osc1 = audioCtx.createOscillator();
        const gain1 = audioCtx.createGain();
        osc1.connect(gain1);
        gain1.connect(audioCtx.destination);
        osc1.frequency.value = 783.99; // G5
        osc1.type = 'sine';
        
        // Second note (C6)
        const osc2 = audioCtx.createOscillator();
        const gain2 = audioCtx.createGain();
        osc2.connect(gain2);
        gain2.connect(audioCtx.destination);
        osc2.frequency.value = 1046.50; // C6
        osc2.type = 'sine';

        const now = audioCtx.currentTime;
        
        // Play G5
        gain1.gain.setValueAtTime(0, now);
        gain1.gain.linearRampToValueAtTime(0.3, now + 0.05);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
        osc1.start(now);
        osc1.stop(now + 0.3);

        // Play C6 slightly after
        gain2.gain.setValueAtTime(0, now + 0.1);
        gain2.gain.linearRampToValueAtTime(0.3, now + 0.15);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
        osc2.start(now + 0.1);
        osc2.stop(now + 0.5);
    } catch (e) {
        console.warn('Audio contextual play blocked or not supported:', e);
    }
};

onMounted(() => {
    // Listen to kitchen channel for realtime orders
    if (window.Echo) {
        window.Echo.channel('kitchen')
            .listen('OrderPlaced', (e) => {
                // Play kitchen bell notification
                playChime();
                
                // Add to active orders list if not already there
                const exists = activeOrders.value.some(o => o.id === e.order.id);
                if (!exists) {
                    activeOrders.value.push(e.order);
                    toast.add({
                        severity: 'info',
                        summary: 'Pesanan Baru!',
                        detail: `Meja ${e.order.table?.number || ''} baru saja memesan`,
                        life: 5000
                    });
                }
            })
            .listen('OrderStatusUpdated', (e) => {
                const index = activeOrders.value.findIndex(o => o.id === e.order.id);
                if (index !== -1) {
                    // If order status is updated to completed/deleted, remove from kitchen
                    if (e.order.status === 'completed' || e.order.status === 'canceled') {
                        activeOrders.value.splice(index, 1);
                    } else {
                        // Otherwise, update the state
                        activeOrders.value[index] = e.order;
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

const updateStatus = async (orderId, newStatus) => {
    try {
        await axios.patch(route('dapur.updateStatus', orderId), { status: newStatus });
        
        // Optimistic update locally
        const index = activeOrders.value.findIndex(o => o.id === orderId);
        if (index !== -1) {
            activeOrders.value[index].status = newStatus;
            toast.add({
                severity: 'success',
                summary: 'Status Diperbarui',
                detail: `Pesanan diubah ke status: ${newStatus === 'cooking' ? 'Memasak' : 'Siap Saji'}`,
                life: 3000
            });
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: 'Gagal mengubah status pesanan.',
            life: 3000
        });
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'pending': return 'danger';
        case 'cooking': return 'warn';
        case 'ready': return 'success';
        default: return 'secondary';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Antrian';
        case 'cooking': return 'Dimasak';
        case 'ready': return 'Selesai';
        default: return status;
    }
};

const formatTime = (timeString) => {
    if (!timeString) return '';
    const date = new Date(timeString);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Monitor Dapur Realtime" />

    <div class="min-h-screen bg-[#0F172A] text-slate-100 p-6 font-sans">
        <Toast />

        <!-- Header -->
        <header class="flex justify-between items-center pb-6 border-b border-slate-800 mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-orange-600/20 text-orange-500 flex items-center justify-center border border-orange-500/30">
                    <i class="pi pi-fire text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight">Dapur Real-time</h1>
                    <p class="text-xs text-slate-400">Memantau pesanan masuk secara langsung</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-ping"></span>
                    <span class="text-sm font-semibold text-green-400">Reverb Terhubung</span>
                </div>
            </div>
        </header>

        <!-- Kitchen Orders Grid -->
        <div v-if="activeOrders.length === 0" class="flex flex-col items-center justify-center py-32 text-slate-500">
            <div class="w-24 h-24 rounded-full bg-slate-800/40 flex items-center justify-center mb-4">
                <i class="pi pi-check text-4xl text-slate-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-400">Tidak Ada Pesanan Aktif</h3>
            <p class="text-sm text-slate-500 mt-1">Dapur Anda sedang santai!</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div 
                v-for="order in activeOrders" 
                :key="order.id"
                :class="[
                    'rounded-2xl border bg-slate-900 overflow-hidden flex flex-col justify-between transition-all duration-300',
                    order.status === 'pending' ? 'border-red-500/30 ring-1 ring-red-500/10 shadow-lg shadow-red-500/5' : 
                    order.status === 'cooking' ? 'border-amber-500/30 ring-1 ring-amber-500/10 shadow-lg shadow-amber-500/5' : 
                    'border-green-500/20'
                ]"
            >
                <!-- Card Header -->
                <div class="p-4 border-b border-slate-800/60 flex justify-between items-start bg-slate-900/60">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-black text-slate-100">Meja {{ order.table?.number || '?' }}</span>
                            <Badge :value="getStatusLabel(order.status)" :severity="getStatusSeverity(order.status)" class="text-[10px] font-extrabold"></Badge>
                        </div>
                        <p class="text-xs text-slate-400 font-medium mt-1">{{ order.customer_name }}</p>
                    </div>
                    <span class="text-xs font-mono text-slate-500 bg-slate-800 px-2 py-1 rounded">{{ formatTime(order.created_at) }}</span>
                </div>

                <!-- Card Body (Order Items) -->
                <div class="p-4 flex-1 space-y-3">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between items-start text-sm">
                        <div class="flex-1 pr-2">
                            <div class="flex gap-2 items-center">
                                <span class="font-bold text-orange-500">{{ item.quantity }}x</span>
                                <span class="font-semibold text-slate-200">{{ item.menu_item?.name }}</span>
                            </div>
                            <p v-if="item.notes" class="text-xs text-amber-500 font-medium italic mt-1 pl-6">
                                <i class="pi pi-info-circle text-[10px]"></i> {{ item.notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Footer (Control Actions) -->
                <div class="p-4 border-t border-slate-800/60 bg-slate-950/40">
                    <Button 
                        v-if="order.status === 'pending' || order.status === 'confirmed'"
                        @click="updateStatus(order.id, 'cooking')"
                        label="Mulai Masak" 
                        icon="pi pi-play" 
                        class="w-full p-3 font-extrabold bg-amber-600 hover:bg-amber-700 border-none rounded-xl text-sm" 
                    />
                    <Button 
                        v-else-if="order.status === 'cooking'"
                        @click="updateStatus(order.id, 'ready')"
                        label="Selesai Masak" 
                        icon="pi pi-check" 
                        class="w-full p-3 font-extrabold bg-green-600 hover:bg-green-700 border-none rounded-xl text-sm" 
                    />
                    <div v-else class="text-center py-2 text-green-500 text-xs font-bold flex items-center justify-center gap-1.5">
                        <i class="pi pi-check-circle"></i> Menunggu Kasir
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
