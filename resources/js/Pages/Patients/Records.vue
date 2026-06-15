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
        <div class="grid gap-6">
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-[2rem] border border-white/10 bg-[#162130] p-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Paciente</p>
                    <h2 class="mt-2 text-2xl font-black text-white">{{ patient.name }}</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ patient.document }}</p>
                </div>
                <Link :href="route('patients.history', patient.patient_id)" class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-black text-slate-200 hover:bg-white/10">Ver historial de citas</Link>
            </section>

            <section class="grid gap-4">
                <article v-for="record in records" :key="record.id" class="rounded-[1.5rem] border border-white/10 bg-[#162130] p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-teal-300">{{ record.type }}</p>
                            <p class="mt-2 text-sm font-bold text-white">{{ record.doctor }}</p>
                        </div>
                        <time class="text-xs font-semibold text-slate-500">{{ record.created_at }}</time>
                    </div>
                    <p class="mt-4 whitespace-pre-wrap text-sm leading-6 text-slate-300">{{ record.content }}</p>
                </article>
                <p v-if="!records.length" class="rounded-[1.5rem] border border-white/10 bg-[#162130] p-6 text-sm text-slate-400">No existen registros clinicos.</p>
            </section>
        </div>
    </DashboardLayout>
</template>
