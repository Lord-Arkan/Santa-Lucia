<script setup>
import { ref, watchEffect, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const style = ref('success');
const message = ref('');
let timer = null;

watchEffect(() => {
    // read both Jetstream banner and generic flash status/message
    style.value = page.props?.jetstream?.flash?.bannerStyle || (page.props?.flash?.status ? 'success' : 'success');
    message.value = page.props?.jetstream?.flash?.banner || page.props?.flash?.status || page.props?.flash?.message || '';

    if (message.value) {
        show.value = true;
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => { show.value = false; }, 4000);
    } else {
        show.value = false;
    }
});

onBeforeUnmount(() => {
    if (timer) clearTimeout(timer);
});
</script>

<template>
    <div class="pointer-events-none fixed inset-x-3 top-3 z-50 sm:inset-x-auto sm:right-4 sm:top-4">
        <div class="flex flex-col items-center space-y-3 sm:items-end">
            <transition
                enter-active-class="transform transition ease-out duration-300"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="show && message" :class="['pointer-events-auto w-full max-w-[340px] overflow-hidden rounded-xl shadow-lg ring-1 ring-black/5 sm:max-w-sm', style == 'success' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white']">
                    <div class="flex items-start gap-2.5 p-3 sm:gap-3 sm:p-4">
                        <div class="flex-shrink-0">
                            <svg v-if="style == 'success'" class="h-5 w-5 text-white sm:h-6 sm:w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else class="h-5 w-5 text-white sm:h-6 sm:w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1 text-xs font-semibold leading-5 sm:text-sm sm:leading-6">
                            {{ message }}
                        </div>

                        <div class="flex-shrink-0">
                            <button @click.prevent="show = false" class="inline-flex text-white hover:opacity-90 focus:outline-none">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>
