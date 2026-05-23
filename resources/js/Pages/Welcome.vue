<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    tables: Array,
});
</script>

<template>
    <Head title="Selamat Datang" />
    <div class="min-h-screen bg-slate-900 text-slate-100 flex flex-col justify-between font-sans selection:bg-emerald-500 selection:text-white">
        <!-- Background Gradient -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-emerald-900/30 via-slate-950 to-slate-950 -z-10"></div>

        <!-- Header -->
        <header class="max-w-7xl w-full mx-auto px-6 py-6 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="bg-gradient-to-tr from-emerald-400 to-teal-500 p-2.5 rounded-xl shadow-lg shadow-emerald-500/20">
                    <i class="pi pi-shop text-slate-950 text-xl font-bold"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">POS Self-Order</h1>
                    <p class="text-xs text-slate-400">Restoran Cepat Saji</p>
                </div>
            </div>
            <nav v-if="canLogin">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="text-sm font-medium text-emerald-400 hover:text-emerald-300 transition-colors flex items-center gap-2"
                >
                    <span>Dashboard Staff</span>
                    <i class="pi pi-arrow-right text-xs"></i>
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-sm font-semibold py-2 px-4 rounded-xl border border-slate-700 transition-all shadow-sm"
                >
                    <i class="pi pi-sign-in text-xs"></i>
                    <span>Login Staff</span>
                </Link>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl w-full mx-auto px-6 py-12 flex-1 flex flex-col items-center justify-center">
            <div class="text-center max-w-2xl mb-12">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Digital Self-Ordering System
                </span>
                <h2 class="text-4xl font-extrabold tracking-tight sm:text-5xl text-white mb-4">
                    Silakan Pilih Meja Anda
                </h2>
                <p class="text-lg text-slate-400">
                    Pilih nomor meja tempat Anda duduk untuk memulai memesan makanan &amp; minuman secara langsung.
                </p>
            </div>

            <!-- Tables Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 w-full max-w-5xl">
                <div
                    v-for="table in tables"
                    :key="table.id"
                    class="group relative rounded-2xl p-6 flex flex-col items-center justify-between border transition-all duration-300"
                    :class="[
                        table.status === 'available'
                            ? 'bg-slate-800/50 hover:bg-slate-800 border-slate-700 hover:border-emerald-500/50 cursor-pointer shadow-lg hover:shadow-emerald-950/20 hover:-translate-y-1'
                            : 'bg-slate-900/40 border-slate-800 opacity-60 cursor-not-allowed hover:border-rose-500/30'
                    ]"
                >
                    <!-- Status Dot -->
                    <span
                        class="absolute top-4 right-4 w-2.5 h-2.5 rounded-full"
                        :class="table.status === 'available' ? 'bg-emerald-400 shadow-lg shadow-emerald-400/50' : 'bg-rose-500'"
                    ></span>

                    <div class="my-4 text-center">
                        <span class="text-xs text-slate-400 uppercase font-semibold tracking-wider">Meja</span>
                        <div class="text-5xl font-black text-white group-hover:text-emerald-400 transition-colors my-1">
                            {{ table.number }}
                        </div>
                    </div>

                    <div class="w-full mt-2">
                        <Link
                            v-if="table.status === 'available'"
                            :href="route('customer.order', table.id)"
                            class="w-full inline-flex items-center justify-center bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-2.5 px-4 rounded-xl transition-all shadow-md shadow-emerald-500/10 text-sm"
                        >
                            Pesan Sekarang
                        </Link>
                        <Link
                            v-else
                            :href="route('customer.order', table.id)"
                            class="w-full inline-flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium py-2.5 px-4 rounded-xl border border-slate-700/50 text-sm transition-all"
                        >
                            Sedang Terisi
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="max-w-7xl w-full mx-auto px-6 py-8 text-center text-xs text-slate-500 border-t border-slate-800">
            <p>&copy; 2026 POS Self-Order System. All rights reserved.</p>
        </footer>
    </div>
</template>
