<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-xs text-rose-400 font-medium mt-1">
            {{ message }}
        </p>
    </div>
</template>
