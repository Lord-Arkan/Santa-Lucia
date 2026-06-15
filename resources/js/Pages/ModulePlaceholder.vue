<script setup>
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head :title="title" />

    <DashboardLayout :title="title">
        <section class="rounded-[2rem] border border-white/10 bg-[#162130] p-8">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">Modulo</p>
            <h2 class="mt-3 text-2xl font-black text-white">{{ title }}</h2>
            <p class="mt-3 max-w-2xl text-sm font-medium text-slate-400">{{ description }}</p>
        </section>
    </DashboardLayout>
</template>
