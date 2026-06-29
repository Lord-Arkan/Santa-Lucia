<script setup>
import DialogModal from './DialogModal.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
    message: { type: String, default: '' },
    confirmText: { type: String, default: 'Confirmar' },
    cancelText: { type: String, default: 'Cancelar' },
    danger: { type: Boolean, default: false },
    maxWidth: { type: String, default: 'sm' },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const close = () => emit('close');
const onConfirm = () => {
    emit('confirm');
    emit('close');
};
const onCancel = () => {
    emit('cancel');
    emit('close');
};
</script>

<template>
    <DialogModal :show="show" :max-width="maxWidth" :closeable="true" boxed box-class="bg-slate-950 max-w-[340px] mx-auto sm:max-w-none" @close="close">
        <template #title>
            <h3 class="text-base font-black text-white sm:text-lg">{{ title }}</h3>
        </template>

        <template #content>
            <p class="text-xs leading-5 text-slate-300 sm:text-sm sm:leading-6">{{ message }}</p>
        </template>

        <template #footer>
            <div class="flex w-full items-center justify-end gap-2">
                <button
                    type="button"
                    class="rounded-xl border border-white/10 px-3 py-2 text-xs font-black text-slate-300 sm:rounded-2xl sm:px-4 sm:text-sm"
                    @click="onCancel"
                >
                    {{ cancelText }}
                </button>

                <button
                    type="button"
                    :class="[
                        'rounded-xl px-3 py-2 text-xs font-black sm:rounded-2xl sm:px-4 sm:text-sm',
                        danger ? 'bg-rose-500 text-white' : 'bg-teal-400 text-slate-950'
                    ]"
                    @click="onConfirm"
                >
                    {{ confirmText }}
                </button>
            </div>
        </template>
    </DialogModal>
</template>
