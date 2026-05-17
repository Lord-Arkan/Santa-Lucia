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
    <button class="btn-primary" :type="type" :disabled="disabled">
        <slot />
    </button>
</template>
