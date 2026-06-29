<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    patient: { type: Object, required: true },
    records: { type: Array, required: true },
});
</script>

<template>
    <Head :title="`Registros - ${patient.name}`" />

    <DashboardLayout title="Registros clinicos">
        <div class="grid gap-4 sm:gap-6">
            <section class="flex flex-col items-start gap-4 rounded-2xl border border-white/10 bg-[#162130] p-4 sm:flex-row sm:items-center sm:justify-between sm:rounded-[2rem] sm:p-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Paciente</p>
                    <h2 class="mt-2 text-xl font-black text-white sm:text-2xl">{{ patient.name }}</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ patient.document }}</p>
                </div>
                <Link :href="route('patients.history', patient.patient_id)" class="w-full rounded-xl border border-white/10 px-4 py-2 text-center text-sm font-black text-slate-200 hover:bg-white/10 sm:w-auto sm:rounded-2xl">Ver historial de citas</Link>
            </section>

            <section class="grid gap-4">
                <article v-for="record in records" :key="record.id" class="rounded-2xl border border-white/10 bg-[#162130] p-4 sm:rounded-[1.5rem] sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-teal-300">{{ record.type }}</p>
                            <p class="mt-2 text-sm font-bold text-white">{{ record.doctor }}</p>
                        </div>
                        <time class="text-xs font-semibold text-slate-500">{{ record.created_at }}</time>
                    </div>
                    <p class="mt-4 whitespace-pre-wrap text-sm leading-6 text-slate-300">{{ record.content }}</p>
                </article>
                <p v-if="!records.length" class="rounded-2xl border border-white/10 bg-[#162130] p-4 text-sm text-slate-400 sm:rounded-[1.5rem] sm:p-6">No existen registros clinicos.</p>
            </section>
        </div>
    </DashboardLayout>
</template>
