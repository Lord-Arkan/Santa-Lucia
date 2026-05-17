<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import KpiCard from '@/Components/ui/KpiCard.vue';
import ScheduleBoard from '@/Components/ui/ScheduleBoard.vue';

const props = defineProps({
    summary: { type: Array, default: () => [] },
    upcomingAppointments: { type: Array, default: () => [] },
    schedule: { type: Object, default: () => ({ days: [], rows: [] }) },
});

const summary = computed(() => props.summary);
const upcomingAppointments = computed(() => props.upcomingAppointments);
const schedule = computed(() => props.schedule);
</script>

<template>
    <Head title="Dashboard" />

    <DashboardLayout title="Dashboard">
        <ScheduleBoard
            title="Calendario de Citas Programadas"
            :upcoming-appointments="upcomingAppointments"
            :schedule="schedule"
        />

        <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3" aria-label="Resumen diario">
            <KpiCard v-for="item in summary" :key="item.id" :item="item" />
        </section>
    </DashboardLayout>
</template>
