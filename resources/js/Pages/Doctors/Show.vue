<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineProps({ doctor: { type: Object, required: true } });
</script>

<template>
    <Head :title="doctor.name" />
    <DashboardLayout title="Detalle del doctor">
        <section class="max-w-3xl rounded-[2rem] border border-white/10 bg-[#162130] p-7">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Profesional medico</p>
            <h2 class="mt-3 text-3xl font-black text-white">{{ doctor.name }}</h2>
            <div class="mt-6 grid gap-3 text-sm text-slate-300 sm:grid-cols-2">
                <p><strong>Especialidad:</strong> {{ doctor.specialty }}</p>
                <p><strong>Licencia:</strong> {{ doctor.license_number }}</p>
                <p><strong>Email:</strong> {{ doctor.email }}</p>
                <p><strong>Estado:</strong> {{ doctor.status }}</p>
            </div>
            <Link :href="route('doctors.index')" class="mt-6 inline-flex rounded-xl border border-white/10 px-4 py-2 text-sm font-black text-slate-200 hover:bg-white/10">Volver</Link>
        </section>
    </DashboardLayout>
</template>
