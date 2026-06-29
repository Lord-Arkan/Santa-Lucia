<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    boxed: {
        type: Boolean,
        default: false,
    },
    boxClass: {
        type: String,
        default: '',
    },
    white: {
        type: Boolean,
        default: false,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="props.show"
        :max-width="props.maxWidth"
        :closeable="props.closeable"
        @close="close"
    >
        <template v-if="props.boxed">
            <div :class="['relative rounded-2xl border border-white/10 p-4 shadow-xl sm:rounded-[2rem] sm:p-6', props.boxClass]">
                <div v-if="$slots.title" :class="['text-lg font-medium', props.white ? 'text-slate-900' : 'text-slate-100']">
                    <slot name="title" />
                </div>

                <div :class="['mt-4 text-sm', props.white ? 'text-slate-700' : 'text-slate-300']">
                    <slot name="content" />
                </div>

                <div class="flex flex-row justify-end mt-6">
                    <slot name="footer" />
                </div>
            </div>
        </template>

        <template v-else>
            <div class="px-3 py-3 sm:px-6 sm:py-4">
                <div v-if="$slots.title" :class="['text-lg font-medium', props.white ? 'text-slate-900' : 'text-slate-100']">
                    <slot name="title" />
                </div>

                <div :class="['mt-4 text-sm', props.white ? 'text-slate-700' : 'text-slate-300']">
                    <slot name="content" />
                </div>
            </div>

            <div :class="['flex flex-row justify-end bg-transparent px-3 py-3 text-end sm:px-6 sm:py-4', props.white ? 'text-slate-700' : 'text-slate-300']">
                <slot name="footer" />
            </div>
        </template>
    </Modal>
</template>
