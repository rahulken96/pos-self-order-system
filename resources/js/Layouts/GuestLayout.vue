<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center bg-slate-950 text-slate-100 font-sans p-6 overflow-hidden">
        <!-- Radial Glow Background -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-emerald-950/20 via-slate-950 to-slate-950 -z-10"></div>
        <div class="absolute w-[400px] h-[400px] rounded-full bg-emerald-500/5 blur-[120px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -z-10 animate-pulse"></div>

        <div class="w-full max-w-md bg-slate-900/60 backdrop-blur-md border border-slate-850/80 p-8 rounded-3xl shadow-2xl relative">
            <!-- Header Brand Brand -->
            <div class="flex flex-col items-center mb-8">
                <Link href="/" class="flex flex-col items-center gap-3 group">
                    <div class="bg-gradient-to-tr from-emerald-400 to-teal-500 p-3 rounded-2xl shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-all">
                        <i class="pi pi-shop text-slate-950 text-2xl font-bold"></i>
                    </div>
                    <div class="text-center">
                        <h2 class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">POS Self-Order</h2>
                        <p class="text-xs text-slate-400 mt-1">Akses Portal Staff Restoran</p>
                    </div>
                </Link>
            </div>

            <!-- Page Slot -->
            <slot />
        </div>
    </div>
</template>
