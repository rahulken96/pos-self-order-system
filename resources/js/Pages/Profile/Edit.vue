<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="p-6">
            <div class="mx-auto max-w-3xl space-y-6">
                <!-- Page Title -->
                <div>
                    <h1 class="text-2xl font-extrabold">Pengaturan Akun</h1>
                    <p class="text-xs text-slate-400">Perbarui informasi profil dan sandi masuk Anda</p>
                </div>

                <div class="bg-slate-900 border border-slate-850 p-6 sm:p-8 rounded-3xl shadow-xl">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="bg-slate-900 border border-slate-850 p-6 sm:p-8 rounded-3xl shadow-xl">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-slate-900 border border-slate-850 p-6 sm:p-8 rounded-3xl shadow-xl">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
