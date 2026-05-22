<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import ToggleButton from 'primevue/togglebutton';
import FileUpload from 'primevue/fileupload';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    menuItems: Array,
    categories: Array,
    filters: Object
});

const toast = useToast();
const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '');

const showFormModal = ref(false);
const isEditing = ref(false);
const selectedItem = ref(null);

const form = ref({
    category_id: '',
    name: '',
    description: '',
    price: 0,
    is_available: true,
    image_file: null
});

const formatRp = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v);

const openCreate = () => {
    isEditing.value = false;
    selectedItem.value = null;
    form.value = {
        category_id: props.categories[0]?.id || '',
        name: '',
        description: '',
        price: 0,
        is_available: true,
        image_file: null
    };
    showFormModal.value = true;
};

const openEdit = (item) => {
    isEditing.value = true;
    selectedItem.value = item;
    form.value = {
        category_id: item.category_id,
        name: item.name,
        description: item.description || '',
        price: Number(item.price),
        is_available: !!item.is_available,
        image_file: null
    };
    showFormModal.value = true;
};

const onFileSelect = (event) => {
    form.value.image_file = event.files[0];
};

const submitForm = () => {
    const formData = new FormData();
    formData.append('category_id', form.value.category_id);
    formData.append('name', form.value.name);
    formData.append('description', form.value.description);
    formData.append('price', form.value.price);
    formData.append('is_available', form.value.is_available ? '1' : '0');
    if (form.value.image_file) {
        formData.append('image_file', form.value.image_file);
    }

    if (isEditing.value) {
        // Laravel doesn't support multipart form-data via PUT directly. Use POST with _method = PUT.
        formData.append('_method', 'PUT');
        router.post(route('menu.update', selectedItem.value.id), formData, {
            onSuccess: () => {
                showFormModal.value = false;
                toast.add({ severity: 'success', summary: 'Sukses', detail: 'Menu berhasil diupdate', life: 3000 });
            },
            onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengupdate menu', life: 3000 })
        });
    } else {
        router.post(route('menu.store'), formData, {
            onSuccess: () => {
                showFormModal.value = false;
                toast.add({ severity: 'success', summary: 'Sukses', detail: 'Menu berhasil dibuat', life: 3000 });
            },
            onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal membuat menu', life: 3000 })
        });
    }
};

const deleteItem = (item) => {
    if (confirm('Hapus menu ini?')) {
        router.delete(route('menu.destroy', item.id), {
            onSuccess: () => toast.add({ severity: 'success', summary: 'Sukses', detail: 'Menu berhasil dihapus', life: 3000 })
        });
    }
};

const applyFilters = () => {
    router.get(route('menu.index'), {
        search: search.value,
        category_id: categoryId.value
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Kelola Menu" />
    <div class="min-h-screen bg-slate-950 text-slate-100 p-6 font-sans">
        <Toast />

        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-slate-800 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold">Kelola Menu Makanan</h1>
                <p class="text-xs text-slate-400">Tambah, ubah, dan atur ketersediaan menu</p>
            </div>
            <div class="flex gap-2">
                <a :href="route('dashboard')">
                    <Button label="Kembali ke Dashboard" icon="pi pi-arrow-left" severity="secondary" text />
                </a>
                <Button label="Tambah Menu Baru" icon="pi pi-plus" class="bg-indigo-600 border-none font-bold" @click="openCreate" />
            </div>
        </header>

        <!-- Filter & Search -->
        <div class="flex flex-col md:flex-row gap-4 mb-8 bg-slate-900 border border-slate-850 p-4 rounded-2xl">
            <div class="flex-1">
                <InputText v-model="search" placeholder="Cari nama menu..." class="w-full" @input="applyFilters" />
            </div>
            <div class="w-full md:w-64">
                <Select
                    v-model="categoryId"
                    :options="categories"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Semua Kategori"
                    class="w-full"
                    showClear
                    @change="applyFilters"
                />
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="item in menuItems" :key="item.id" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Image -->
                    <div class="h-44 w-full bg-slate-950 relative flex items-center justify-center overflow-hidden">
                        <img v-if="item.image" :src="item.image" class="w-full h-full object-cover" alt="" />
                        <div v-else class="text-slate-650 flex flex-col items-center">
                            <i class="pi pi-image text-3xl"></i>
                            <span class="text-[10px] mt-1">No Image</span>
                        </div>
                        <span :class="[
                            'absolute top-3 right-3 text-[10px] font-bold px-2 py-0.5 rounded-full border',
                            item.is_available ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-red-500/10 border-red-500/20 text-red-400'
                        ]">
                            {{ item.is_available ? 'Tersedia' : 'Habis' }}
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="p-4 space-y-2">
                        <span class="text-[10px] font-semibold text-indigo-400 uppercase tracking-wider">{{ item.category?.name }}</span>
                        <h3 class="font-bold text-base truncate">{{ item.name }}</h3>
                        <p class="text-xs text-slate-400 line-clamp-2 h-8">{{ item.description || '-' }}</p>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="p-4 border-t border-slate-800/60 flex justify-between items-center bg-slate-900/40">
                    <span class="font-extrabold text-sm text-emerald-400">{{ formatRp(item.price) }}</span>
                    <div class="flex gap-2">
                        <Button icon="pi pi-pencil" severity="secondary" text class="w-8 h-8 p-0" @click="openEdit(item)" />
                        <Button icon="pi pi-trash" severity="danger" text class="w-8 h-8 p-0" @click="deleteItem(item)" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <Dialog v-model:visible="showFormModal" :header="isEditing ? 'Ubah Menu' : 'Tambah Menu Baru'" modal :style="{ width: '95vw', maxWidth: '500px' }">
            <form @submit.prevent="submitForm" class="pt-4 space-y-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Nama Menu</label>
                    <InputText v-model="form.name" required placeholder="Contoh: Nasi Goreng Spesial" class="w-full" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Kategori</label>
                    <Select v-model="form.category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="Pilih Kategori" class="w-full" required />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Deskripsi</label>
                    <Textarea v-model="form.description" rows="3" placeholder="Masukkan deskripsi menu..." class="w-full" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Harga (Rp)</label>
                    <InputNumber v-model="form.price" mode="decimal" :min="0" class="w-full" required />
                </div>

                <div class="flex justify-between items-center border border-slate-850 p-3 rounded-xl">
                    <span class="text-xs font-semibold text-slate-300">Tersedia</span>
                    <ToggleButton v-model="form.is_available" onLabel="Tersedia" offLabel="Habis" onIcon="pi pi-check" offIcon="pi pi-times" class="w-32" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-slate-300">Foto Menu</label>
                    <FileUpload mode="basic" chooseLabel="Pilih Foto" @select="onFileSelect" accept="image/*" class="w-full" />
                    <span v-if="form.image_file" class="text-xs text-indigo-400 font-medium">Selected: {{ form.image_file.name }}</span>
                </div>

                <div class="flex gap-2 pt-4">
                    <Button label="Batal" severity="secondary" text @click="showFormModal = false" class="flex-1" />
                    <Button type="submit" label="Simpan" class="bg-indigo-600 border-none flex-1 font-bold" />
                </div>
            </form>
        </Dialog>
    </div>
</template>
