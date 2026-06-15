<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({ patients: { type: Array, required: true } });
</script>

<template>
    <Head title="Historial clinico" />

    <DashboardLayout title="Historial clinico">
        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[#162130]">
            <div class="border-b border-white/10 p-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Pacientes autorizados</p>
            </div>
            <div v-if="patients.length" class="divide-y divide-white/10">
                <article v-for="patient in patients" :key="patient.patient_id" class="flex flex-wrap items-center justify-between gap-4 p-5">
                    <div>
                        <p class="font-black text-white">{{ patient.name }}</p>
                        <p class="mt-1 text-sm text-slate-400">{{ patient.document }}</p>
                        <p class="mt-2 text-xs font-semibold text-slate-500">{{ patient.appointments_count }} citas · {{ patient.clinical_records_count }} registros clinicos</p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('patients.history', patient.patient_id)" class="rounded-xl border border-sky-300/20 px-3 py-2 text-xs font-black text-sky-200 hover:bg-sky-400/10">Historial</Link>
                        <Link :href="route('patients.records.index', patient.patient_id)" class="rounded-xl border border-violet-300/20 px-3 py-2 text-xs font-black text-violet-200 hover:bg-violet-400/10">Registros</Link>
                    </div>
                </article>
            </div>
            <p v-else class="p-6 text-sm text-slate-400">No hay pacientes relacionados disponibles.</p>
        </section>
    </DashboardLayout>
</template>
