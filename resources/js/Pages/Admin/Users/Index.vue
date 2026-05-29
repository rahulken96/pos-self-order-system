<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    users: Array
});

const toast = useToast();
const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedUser = ref(null);

const formAdd = ref({
    name: '',
    email: '',
    role: 'kasir',
    password: ''
});

const formEdit = ref({
    name: '',
    email: '',
    role: 'kasir',
    password: ''
});

const openAdd = () => {
    formAdd.value = {
        name: '',
        email: '',
        role: 'kasir',
        password: ''
    };
    showAddModal.value = true;
};

const openEdit = (user) => {
    selectedUser.value = user;
    formEdit.value = {
        name: user.name,
        email: user.email,
        role: user.role,
        password: ''
    };
    showEditModal.value = true;
};

const submitAdd = () => {
    router.post(route('users.store'), formAdd.value, {
        onSuccess: () => {
            showAddModal.value = false;
            toast.add({ severity: 'success', summary: 'Sukses', detail: 'User berhasil ditambahkan', life: 3000 });
        },
        onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menambahkan user', life: 3000 })
    });
};

const submitEdit = () => {
    router.put(route('users.update', selectedUser.value.id), formEdit.value, {
        onSuccess: () => {
            showEditModal.value = false;
            toast.add({ severity: 'success', summary: 'Sukses', detail: 'User berhasil diupdate', life: 3000 });
        },
        onError: () => toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengupdate user', life: 3000 })
    });
};

const deleteUser = (user) => {
    if (confirm(`Hapus user ${user.name}?`)) {
        router.delete(route('users.destroy', user.id), {
            onSuccess: () => toast.add({ severity: 'success', summary: 'Sukses', detail: 'User berhasil dihapus', life: 3000 }),
            onError: (err) => {
                const msg = err.error || 'Gagal menghapus user';
                toast.add({ severity: 'error', summary: 'Gagal', detail: msg, life: 3000 });
            }
        });
    }
};

const getRoleBadge = (role) => {
    if (role === 'admin') return 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400';
    if (role === 'kasir') return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400';
    return 'bg-amber-500/10 border-amber-500/20 text-amber-400';
};
</script>

<template>
    <Head title="Kelola User" />
    <AuthenticatedLayout>
        <div class="p-6">
            <Toast />

            <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-slate-800 mb-8">
                <div>
                    <h1 class="text-2xl font-extrabold">Kelola Staff</h1>
                    <p class="text-xs text-slate-400">Atur akun akses untuk Admin, Kasir, dan Kitchen Staff</p>
                </div>
                <div class="flex gap-2">
                    <Button label="Tambah Staff" icon="pi pi-plus" class="bg-indigo-600 border-none font-bold" @click="openAdd" />
                </div>
            </header>

        <!-- User Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="user in users" :key="user.id" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between space-y-4">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-850 border border-slate-700 flex items-center justify-center text-indigo-400 font-bold">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h3 class="font-bold text-base line-clamp-1">{{ user.name }}</h3>
                            <p class="text-[11px] text-slate-400">{{ user.email }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center bg-slate-950/40 p-2.5 rounded-xl border border-slate-850/60">
                    <span :class="['text-[10px] font-bold px-2 py-0.5 rounded-full border uppercase tracking-wider', getRoleBadge(user.role)]">
                        {{ user.role }}
                    </span>
                    <div class="flex gap-1.5">
                        <Button icon="pi pi-pencil" severity="secondary" text class="w-8 h-8 p-0" @click="openEdit(user)" />
                        <Button icon="pi pi-trash" severity="danger" text class="w-8 h-8 p-0" :disabled="$page.props.auth.user.id === user.id" @click="deleteUser(user)" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Dialog -->
        <Dialog v-model:visible="showAddModal" header="Tambah Staff Baru" modal :style="{ width: '90vw', maxWidth: '400px' }">
            <form @submit.prevent="submitAdd" class="pt-4 space-y-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Nama Lengkap</label>
                    <InputText v-model="formAdd.name" required class="w-full" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Email</label>
                    <InputText v-model="formAdd.email" type="email" required class="w-full" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Role</label>
                    <Select
                        v-model="formAdd.role"
                        :options="[
                            { label: 'Admin', value: 'admin' },
                            { label: 'Kasir', value: 'kasir' },
                            { label: 'Dapur', value: 'dapur' }
                        ]"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                    />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Password</label>
                    <InputText v-model="formAdd.password" type="password" required class="w-full" />
                </div>
                <div class="flex gap-2 pt-4">
                    <Button label="Batal" severity="secondary" text @click="showAddModal = false" class="flex-1" />
                    <Button type="submit" label="Tambah" class="bg-indigo-600 border-none flex-1 font-bold" />
                </div>
            </form>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:visible="showEditModal" :header="`Ubah Staff ${selectedUser?.name}`" modal :style="{ width: '90vw', maxWidth: '400px' }">
            <form @submit.prevent="submitEdit" class="pt-4 space-y-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Nama Lengkap</label>
                    <InputText v-model="formEdit.name" required class="w-full" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Email</label>
                    <InputText v-model="formEdit.email" type="email" required class="w-full" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Role</label>
                    <Select
                        v-model="formEdit.role"
                        :options="[
                            { label: 'Admin', value: 'admin' },
                            { label: 'Kasir', value: 'kasir' },
                            { label: 'Dapur', value: 'dapur' }
                        ]"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                    />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-300">Password Baru (Kosongkan jika tidak diubah)</label>
                    <InputText v-model="formEdit.password" type="password" class="w-full" placeholder="Password baru" />
                </div>
                <div class="flex gap-2 pt-4">
                    <Button label="Batal" severity="secondary" text @click="showEditModal = false" class="flex-1" />
                    <Button type="submit" label="Simpan" class="bg-indigo-600 border-none flex-1 font-bold" />
                </div>
            </form>
        </Dialog>
        </div>
    </AuthenticatedLayout>
</template>
