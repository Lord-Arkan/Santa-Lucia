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
    <DialogModal :show="show" :max-width="maxWidth" :closeable="true" boxed box-class="bg-slate-950" @close="close">
        <template #title>
            {{ title }}
        </template>

        <template #content>
            <p class="text-sm text-slate-300">{{ message }}</p>
        </template>

        <template #footer>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-black text-slate-300"
                    @click="onCancel"
                >
                    {{ cancelText }}
                </button>

                <button
                    type="button"
                    :class="[
                        'rounded-2xl px-4 py-2 text-sm font-black',
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
