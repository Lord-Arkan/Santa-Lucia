<script setup>
defineProps({
    disabled: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        class="h-[42px] w-full rounded-lg bg-gradient-to-r from-teal-600 to-teal-500 px-4 text-[14px] font-semibold text-white shadow-sm shadow-teal-700/20 transition hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-teal-200 disabled:cursor-not-allowed disabled:opacity-70"
        :type="type"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>
