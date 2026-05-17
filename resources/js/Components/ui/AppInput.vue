<script setup>
defineProps({
    id: {
        type: String,
        required: true,
    },
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    placeholder: {
        type: String,
        default: '',
    },
    autocomplete: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    icon: {
        type: String,
        default: 'user',
    },
    trailingIcon: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'trailing-icon-click']);

const onInput = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <div>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400" aria-hidden="true">
                <svg v-if="icon === 'user'" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.97 0-9 2.24-9 5v1h18v-1c0-2.76-4.03-5-9-5Z" />
                </svg>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17 8h-1V6a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2Zm-3 0h-4V6a2 2 0 1 1 4 0Z" />
                </svg>
            </span>

            <input
                :id="id"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :autocomplete="autocomplete"
                class="h-[42px] w-full rounded-lg border border-slate-300 bg-white pl-9 pr-9 text-[14px] text-slate-700 placeholder:text-slate-400 transition focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100"
                @input="onInput"
            >

            <button
                v-if="trailingIcon"
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                @click="$emit('trailing-icon-click')"
            >
                <span class="sr-only">Mostrar u ocultar password</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="m2 4 2-2 18 18-2 2-3.7-3.7A12.7 12.7 0 0 1 12 19c-5 0-9.27-3.11-11-7.5a12.89 12.89 0 0 1 4.2-5.59Zm7.78 7.78a3 3 0 0 0 4.44 4.44ZM12 5a9.9 9.9 0 0 1 11 6.5 12.5 12.5 0 0 1-3.12 4.65L16.9 13.2A5 5 0 0 0 10.8 7.1L8.9 5.2A10.58 10.58 0 0 1 12 5Z" />
                </svg>
            </button>
        </div>

        <p v-if="error" class="mt-1 text-xs font-medium text-rose-600">{{ error }}</p>
    </div>
</template>
