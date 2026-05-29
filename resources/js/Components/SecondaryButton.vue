<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-xl border border-slate-800 bg-slate-900 hover:bg-slate-850 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-slate-300 hover:text-slate-100 shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-slate-700 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
