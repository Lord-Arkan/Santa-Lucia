<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import AppointmentForm from '@/Components/appointments/AppointmentForm.vue';
import { appointmentService } from '@/services/appointmentService';
import { toRelativeUrl } from '@/utils/navigation';

const props = defineProps({
    doctors: { type: Array, default: () => [] },
    patients: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    specialties: { type: Array, default: () => [] },
    my_doctor: { type: Object, default: null },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user ?? null);
const isDoctor = computed(() => currentUser.value && currentUser.value.rol === 'doctor');

const form = useForm(appointmentService.defaultForm());

if (isDoctor.value && props.my_doctor) {
    form.doctor_id = props.my_doctor.doctor_id;
}

const submit = (localForm) => {
    const options = { preserveScroll: true, onSuccess: () => router.get(toRelativeUrl(route('appointments.index'))) };
    localForm.post(route('appointments.store'), options);
};

const cancel = () => router.get(toRelativeUrl(route('appointments.index')));
</script>

<template>
    <Head title="Nueva cita" />

    <DashboardLayout>
        <div class="grid gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-white">Reservar cita</h2>
                    <p class="text-sm text-slate-400">Complete los datos para reservar una cita.</p>
                </div>
            </div>

            <div>
                <AppointmentForm :form="form" :doctors="props.doctors" :patients="props.patients" :services="props.services" :specialties="props.specialties" :is-doctor="isDoctor" :my-doctor="props.my_doctor" @submit="submit" @cancel="cancel" />
            </div>
        </div>
    </DashboardLayout>
</template>
