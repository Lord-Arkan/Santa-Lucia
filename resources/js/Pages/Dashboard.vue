<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import KpiCard from '@/components/ui/KpiCard.vue';
import ScheduleBoard from '@/components/ui/ScheduleBoard.vue';
import { dashboardService } from '@/services/dashboardService';

const snapshot = dashboardService.getDashboardSnapshot();

const summary = computed(() => snapshot.summary);
const upcomingAppointments = computed(() => snapshot.upcomingAppointments);
const schedule = computed(() => snapshot.schedule);
</script>

<template>
    <Head title="Dashboard" />

    <DashboardLayout title="Resumen de Citas de Hoy">
        <section class="kpi-grid" aria-label="Resumen diario">
            <KpiCard v-for="item in summary" :key="item.id" :item="item" />
        </section>

        <ScheduleBoard
            title="Calendario de Citas Programadas"
            :upcoming-appointments="upcomingAppointments"
            :schedule="schedule"
        />
    </DashboardLayout>
</template>
