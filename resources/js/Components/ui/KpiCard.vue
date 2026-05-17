<script setup>
import { computed } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const variant = computed(() => {
    const variants = {
        'kpi-red': {
            border: 'border-rose-400/20',
            icon: 'bg-rose-400/10 text-rose-300',
            trend: 'text-rose-300 bg-rose-400/10',
            accent: 'from-rose-400/20',
            label: 'Revisar',
        },
        'kpi-amber': {
            border: 'border-amber-300/20',
            icon: 'bg-amber-300/10 text-amber-200',
            trend: 'text-amber-200 bg-amber-300/10',
            accent: 'from-amber-300/20',
            label: 'Pendiente',
        },
        'kpi-green': {
            border: 'border-teal-300/20',
            icon: 'bg-teal-300/10 text-teal-200',
            trend: 'text-teal-200 bg-teal-300/10',
            accent: 'from-teal-300/20',
            label: 'Activo',
        },
    };

    return variants[props.item.variant] ?? variants['kpi-green'];
});
</script>

<template>
    <article class="relative overflow-hidden rounded-[1.75rem] border bg-[#162130] p-5 shadow-xl shadow-slate-950/10" :class="variant.border">
        <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b to-transparent" :class="variant.accent" />

        <div class="relative flex items-start justify-between gap-4">
            <div class="grid size-12 place-items-center rounded-2xl" :class="variant.icon">
                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" aria-hidden="true">
                    <path d="M7 3v3M17 3v3M4 8h16M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                    <path d="M9 13h6M12 10v6" />
                </svg>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-black" :class="variant.trend">{{ variant.label }}</span>
        </div>

        <div class="relative mt-8">
            <p class="text-4xl font-black tracking-tight text-white">{{ item.count }}</p>
            <p class="mt-3 text-base font-black text-slate-200">{{ item.title }}</p>
            <p class="mt-1 text-sm leading-5 text-slate-500">{{ item.description }}</p>
        </div>
    </article>
</template>
