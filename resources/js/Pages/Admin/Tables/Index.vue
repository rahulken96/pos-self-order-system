<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    tables: Array
});

const toast = useToast();
const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedTable = ref(null);

const formAdd = ref({
    number: ''
});

const formEdit = ref({
    number: '',
    status: 'available'
});

const openAdd = () => {
    formAdd.value.number = '';
    showAddModal.value = true;
};

const openEdit = (table) => {
    selectedTable.value = table;
    formEdit.value = {
        number: table.number,
        status: table.status
    };
    showEditModal.value = true;
};

const submitAdd = () => {
    router.post(route('tables.store'), formAdd.value, {
        onSuccess: () => {
            showAddModal.value = false;
            toast.add({ severity: 'success', summary: 'Sukses', detail: 'Meja berhasil ditambahkan', life: 3000 });
        },
        onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menambahkan meja', life: 3000 })
    });
};

const submitEdit = () => {
    router.put(route('tables.update', selectedTable.value.id), formEdit.value, {
        onSuccess: () => {
            showEditModal.value = false;
            toast.add({ severity: 'success', summary: 'Sukses', detail: 'Meja berhasil diupdate', life: 3000 });
        },
        onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengupdate meja', life: 3000 })
    });
};

const deleteTable = (table) => {
    if (confirm(`Hapus meja ${table.number}?`)) {
        router.delete(route('tables.destroy', table.id), {
            onSuccess: () => toast.add({ severity: 'success', summary: 'Sukses', detail: 'Meja berhasil dihapus', life: 3000 })
        });
    }
};

const regenerateQr = (table) => {
    router.post(route('tables.qr', table.id), {}, {
        onSuccess: () => toast.add({ severity: 'success', summary: 'Sukses', detail: 'QR Code berhasil dibuat ulang', life: 3000 })
    });
};

const downloadQr = (table) => {
    if (!table.qr_code) return;
    const link = document.createElement('a');
    link.href = `data:image/svg+xml;base64,${table.qr_code}`;
    link.download = `qr-meja-${table.number}.svg`;
    link.click();
};
</script>

<template>
    <Head title="Kelola Meja" />
    <div class="min-h-screen bg-slate-950 text-slate-100 p-6 font-sans">
        <Toast />

        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-slate-800 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold">Kelola Meja & QR Code</h1>
                <p class="text-xs text-slate-400">Atur nomor meja dan unduh QR Code untuk pemesanan mandiri</p>
            </div>
            <div class="flex gap-2">
                <a :href="route('dashboard')">
                    <Button label="Kembali ke Dashboard" icon="pi pi-arrow-left" severity="secondary" text />
                </a>
                <Button label="Tambah Meja Baru" icon="pi pi-plus" class="bg-indigo-600 border-none font-bold" @click="openAdd" />
            </div>
        </header>

        <!-- Tables Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="table in tables" :key="table.id" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-3xl font-black">Meja {{ table.number }}</h2>
                        <span :class="[
                            'inline-block text-[10px] font-bold px-2 py-0.5 rounded-full border mt-1.5',
                            table.status === 'available' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-amber-500/10 border-amber-500/20 text-amber-400'
                        ]">
                            {{ table.status === 'available' ? 'Tersedia' : 'Terisi' }}
                        </span>
                    </div>
                    <div class="flex gap-1.5">
                        <Button icon="pi pi-pencil" severity="secondary" text class="w-8 h-8 p-0" @click="openEdit(table)" />
                        <Button icon="pi pi-trash" severity="danger" text class="w-8 h-8 p-0" @click="deleteTable(table)" />
                    </div>
                </div>

                <!-- QR Display -->
                <div class="bg-white rounded-xl p-3 aspect-square flex flex-col items-center justify-center relative group">
                    <img v-if="table.qr_code" :src="`data:image/svg+xml;base64,${table.qr_code}`" class="w-full h-full object-contain" alt="QR Code" />
                    <div v-else class="text-slate-400 flex flex-col items-center">
                        <i class="pi pi-qrcode text-4xl"></i>
                        <span class="text-[10px] mt-2">Belum ada QR Code</span>
                    </div>
                </div>

                <!-- QR Action -->
                <div class="flex gap-2">
                    <Button label="Regenerate QR" icon="pi pi-refresh" severity="secondary" text class="flex-1 text-xs" @click="regenerateQr(table)" />
                    <Button label="Download SVG" icon="pi pi-download" class="bg-slate-800 border-slate-700 flex-1 text-xs" :disabled="!table.qr_code" @click="downloadQr(table)" />
                </div>
            </div>
        </div>

        <!-- Add Dialog -->
        <Dialog v-model:visible="showAddModal" header="Tambah Meja Baru" modal :style="{ width: '90vw', maxWidth: '400px' }">
            <form @submit.prevent="submitAdd" class="pt-4 space-y-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Nomor Meja</label>
                    <InputText v-model="formAdd.number" required placeholder="Contoh: 12A atau 15" class="w-full" />
                </div>
                <div class="flex gap-2 pt-4">
                    <Button label="Batal" severity="secondary" text @click="showAddModal = false" class="flex-1" />
                    <Button type="submit" label="Tambah" class="bg-indigo-600 border-none flex-1 font-bold" />
                </div>
            </form>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:visible="showEditModal" :header="`Ubah Meja ${selectedTable?.number}`" modal :style="{ width: '90vw', maxWidth: '400px' }">
            <form @submit.prevent="submitEdit" class="pt-4 space-y-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Nomor Meja</label>
                    <InputText v-model="formEdit.number" required class="w-full" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Status Meja</label>
                    <Select
                        v-model="formEdit.status"
                        :options="[
                            { label: 'Tersedia', value: 'available' },
                            { label: 'Terisi', value: 'occupied' }
                        ]"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                    />
                </div>
                <div class="flex gap-2 pt-4">
                    <Button label="Batal" severity="secondary" text @click="showEditModal = false" class="flex-1" />
                    <Button type="submit" label="Simpan" class="bg-indigo-600 border-none flex-1 font-bold" />
                </div>
            </form>
        </Dialog>
    </div>
</template>
