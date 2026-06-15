<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({
    patient: { type: Object, required: true },
    appointments: { type: Array, required: true },
    clinicalRecords: { type: Array, required: true },
});
</script>

<template>
    <Head :title="`Historial - ${patient.name}`" />

    <DashboardLayout title="Historial clinico">
        <div class="grid gap-6">
            <section class="rounded-[2rem] border border-white/10 bg-[#162130] p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Paciente</p>
                        <h2 class="mt-2 text-2xl font-black text-white">{{ patient.name }}</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ patient.document }}</p>
                    </div>
                    <Link :href="route('patients.records.index', patient.patient_id)" class="rounded-2xl bg-teal-400 px-4 py-2 text-sm font-black text-slate-950">
                        Ver registros clinicos
                    </Link>
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl bg-white/5 p-4 text-sm text-slate-300"><strong>Alergias:</strong> {{ patient.allergies || 'Sin registro' }}</div>
                    <div class="rounded-2xl bg-white/5 p-4 text-sm text-slate-300"><strong>Antecedentes:</strong> {{ patient.previous_conditions || 'Sin registro' }}</div>
                </div>
            </section>

            <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[#162130]">
                <div class="border-b border-white/10 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Historial de citas</p>
                </div>
                <div v-if="appointments.length" class="divide-y divide-white/10">
                    <article v-for="appointment in appointments" :key="appointment.appointment_id" class="flex flex-wrap items-center justify-between gap-4 p-5">
                        <div>
                            <p class="font-black text-white">{{ appointment.service || 'Consulta' }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ appointment.appointment_date }} {{ appointment.start_time }} · {{ appointment.doctor }}</p>
                            <p class="mt-1 text-xs font-bold uppercase text-teal-200">{{ appointment.status }}</p>
                        </div>
                        <Link v-if="appointment.detail_url" :href="appointment.detail_url" class="rounded-xl border border-white/10 px-3 py-2 text-xs font-black text-sky-200 hover:bg-white/10">Detalle</Link>
                    </article>
                </div>
                <p v-else class="p-6 text-sm text-slate-400">Este paciente no tiene citas registradas.</p>
            </section>

            <section class="rounded-[2rem] border border-white/10 bg-[#162130] p-5">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Informacion clinica relacionada</p>
                <div v-if="clinicalRecords.length" class="mt-4 grid gap-3">
                    <article v-for="record in clinicalRecords" :key="record.id" class="rounded-2xl bg-white/5 p-4">
                        <div class="flex flex-wrap justify-between gap-2">
                            <p class="text-sm font-black text-white">{{ record.type }} · {{ record.doctor }}</p>
                            <time class="text-xs font-semibold text-slate-500">{{ record.created_at }}</time>
                        </div>
                        <p class="mt-3 whitespace-pre-wrap text-sm text-slate-300">{{ record.content }}</p>
                    </article>
                </div>
                <p v-else class="mt-4 text-sm text-slate-400">Sin registros clinicos asociados.</p>
            </section>
        </div>
    </DashboardLayout>
</template>
