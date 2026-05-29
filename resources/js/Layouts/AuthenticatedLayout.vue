<script setup>
import { ref, computed } from 'vue';
import { Link, Head } from '@inertiajs/vue3';

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// Define menu items dynamically by user role
const getMenuItems = (role) => {
    if (role === 'admin') {
        return [
            { label: 'Laporan & Analitik', routeName: 'admin.reports', icon: 'pi pi-chart-bar' },
            { label: 'Kelola Menu', routeName: 'menu.index', icon: 'pi pi-book' },
            { label: 'Kelola Meja', routeName: 'tables.index', icon: 'pi pi-table' },
            { label: 'Kelola Staff', routeName: 'users.index', icon: 'pi pi-users' }
        ];
    } else if (role === 'kasir') {
        return [
            { label: 'Antrean & Kasir', routeName: 'kasir.index', icon: 'pi pi-credit-card' }
        ];
    } else if (role === 'dapur') {
        return [
            { label: 'Antrean Dapur', routeName: 'dapur.index', icon: 'pi pi-server' }
        ];
    }
    return [];
};
</script>

<template>
    <div class="flex min-h-screen bg-slate-950 text-slate-100 font-sans">
        <!-- Sidebar for Desktop -->
        <aside class="hidden md:flex flex-col w-64 bg-slate-900 border-r border-slate-850 shrink-0 select-none">
            <!-- Brand header -->
            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-850/80">
                <div class="bg-gradient-to-tr from-emerald-400 to-teal-500 p-2 rounded-xl shadow-lg shadow-emerald-500/20">
                    <i class="pi pi-shop text-slate-950 text-base font-bold"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold tracking-tight bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">POS Self-Order</h2>
                    <p class="text-[10px] text-slate-400">Portal Staff Restoran</p>
                </div>
            </div>

            <!-- Profile Info Card -->
            <div class="p-4 mx-4 my-4 rounded-2xl bg-slate-950/40 border border-slate-850/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-slate-950 font-black text-sm uppercase">
                    {{ $page.props.auth.user.name.charAt(0) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-200 truncate">{{ $page.props.auth.user.name }}</p>
                    <span class="inline-block text-[9px] font-bold text-emerald-400 uppercase bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/15 mt-0.5">
                        {{ $page.props.auth.user.role }}
                    </span>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="item in getMenuItems($page.props.auth.user.role)"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 group border"
                    :class="[
                        route().current(item.routeName) || ($page.url.startsWith('/admin/menu') && item.routeName === 'menu.index') || ($page.url.startsWith('/admin/tables') && item.routeName === 'tables.index') || ($page.url.startsWith('/admin/users') && item.routeName === 'users.index')
                            ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 shadow-sm shadow-emerald-950/20'
                            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-850/40 border-transparent'
                    ]"
                >
                    <i :class="[item.icon, route().current(item.routeName) ? 'text-emerald-400' : 'text-slate-500 group-hover:text-slate-300']" class="text-base"></i>
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <!-- Bottom Actions -->
            <div class="p-4 border-t border-slate-850 space-y-1">
                <Link
                    :href="route('profile.edit')"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-850/40 transition-all border border-transparent"
                    :class="{ 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': route().current('profile.edit') }"
                >
                    <i class="pi pi-user-edit text-base" :class="{ 'text-emerald-400': route().current('profile.edit') }"></i>
                    <span>Pengaturan Akun</span>
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-rose-400 hover:text-rose-350 hover:bg-rose-500/5 transition-all text-left border border-transparent cursor-pointer"
                >
                    <i class="pi pi-sign-out text-base"></i>
                    <span>Log Out</span>
                </Link>
            </div>
        </aside>

        <!-- Mobile Header & Navigation Drawer -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            <!-- Mobile Top Navbar -->
            <header class="md:hidden flex items-center justify-between px-6 py-4 border-b border-slate-850 bg-slate-900/60 backdrop-blur-md sticky top-0 z-40 select-none">
                <div class="flex items-center gap-2">
                    <div class="bg-gradient-to-tr from-emerald-400 to-teal-500 p-1.5 rounded-lg shadow shadow-emerald-500/20">
                        <i class="pi pi-shop text-slate-950 text-xs font-bold"></i>
                    </div>
                    <span class="font-extrabold text-sm tracking-tight bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">POS Self-Order</span>
                </div>
                <button
                    @click="toggleMobileMenu"
                    class="p-2 -mr-2 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-850 focus:outline-none transition-colors border border-slate-800"
                >
                    <i :class="isMobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'" class="text-base"></i>
                </button>
            </header>

            <!-- Mobile Drawer Overlay -->
            <div
                v-if="isMobileMenuOpen"
                @click="toggleMobileMenu"
                class="md:hidden fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm transition-opacity"
            ></div>

            <!-- Mobile Navigation Drawer -->
            <aside
                class="md:hidden fixed top-0 bottom-0 left-0 z-50 w-64 bg-slate-900 border-r border-slate-850 flex flex-col transition-transform duration-300 ease-in-out transform select-none"
                :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-850/80">
                    <div class="flex items-center gap-2">
                        <div class="bg-gradient-to-tr from-emerald-400 to-teal-500 p-1.5 rounded-lg shadow shadow-emerald-500/20">
                            <i class="pi pi-shop text-slate-950 text-xs font-bold"></i>
                        </div>
                        <span class="font-bold text-sm tracking-tight text-white">POS Self-Order</span>
                    </div>
                    <button @click="toggleMobileMenu" class="text-slate-400 hover:text-white p-1">
                        <i class="pi pi-times text-sm"></i>
                    </button>
                </div>

                <!-- Profile Info Card -->
                <div class="p-4 mx-4 my-4 rounded-2xl bg-slate-950/40 border border-slate-850/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-slate-950 font-black text-xs uppercase">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-200 truncate">{{ $page.props.auth.user.name }}</p>
                        <span class="inline-block text-[8px] font-bold text-emerald-400 uppercase bg-emerald-500/10 px-1.5 py-0.2 rounded border border-emerald-500/15 mt-0.5">
                            {{ $page.props.auth.user.role }}
                        </span>
                    </div>
                </div>

                <!-- Nav Menu -->
                <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
                    <Link
                        v-for="item in getMenuItems($page.props.auth.user.role)"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        @click="toggleMobileMenu"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 group border"
                        :class="[
                            route().current(item.routeName) || ($page.url.startsWith('/admin/menu') && item.routeName === 'menu.index') || ($page.url.startsWith('/admin/tables') && item.routeName === 'tables.index') || ($page.url.startsWith('/admin/users') && item.routeName === 'users.index')
                                ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                : 'text-slate-400 hover:text-slate-250 hover:bg-slate-850/40 border-transparent'
                        ]"
                    >
                        <i :class="[item.icon, route().current(item.routeName) ? 'text-emerald-400' : 'text-slate-500']" class="text-sm"></i>
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>

                <!-- Bottom Actions -->
                <div class="p-4 border-t border-slate-850 space-y-1">
                    <Link
                        :href="route('profile.edit')"
                        @click="toggleMobileMenu"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-850/40 transition-all border border-transparent"
                        :class="{ 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': route().current('profile.edit') }"
                    >
                        <i class="pi pi-user-edit text-sm" :class="{ 'text-emerald-400': route().current('profile.edit') }"></i>
                        <span>Pengaturan Akun</span>
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex w-full items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-rose-400 hover:bg-rose-500/5 transition-all text-left border border-transparent cursor-pointer"
                    >
                        <i class="pi pi-sign-out text-sm"></i>
                        <span>Log Out</span>
                    </Link>
                </div>
            </aside>

            <!-- Page Main Slot Panel -->
            <main class="flex-grow flex flex-col min-w-0">
                <slot />
            </main>
        </div>
    </div>
</template>
