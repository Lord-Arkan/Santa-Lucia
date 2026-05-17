<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import AppointmentTable from '@/Components/appointments/AppointmentTable.vue';
// Creation handled on a separate page
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { appointmentService } from '@/services/appointmentService';

const props = defineProps({
    appointments: { type: Object, required: true },
    doctors: { type: Array, default: () => [] },
    patients: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    my_doctor: { type: Object, default: null },
});

const page = usePage();
const showFilters = ref(false);
const currentUser = computed(() => page.props.auth?.user ?? null);
const isDoctor = computed(() => currentUser.value && currentUser.value.rol === 'doctor');
const myDoctor = computed(() => page.props.my_doctor ?? null);

const editingAppointment = ref(null);
const status = computed(() => page.props.flash?.status ?? '');

const form = useForm(appointmentService.defaultForm());

const resetForm = () => {
    editingAppointment.value = null;
    form.reset();
    form.clearErrors();
    form.status = 'SCHEDULED';
};

const openCreate = () => {
    router.get(route('appointments.create'));
};

const submit = (localForm) => {
    const options = { preserveScroll: true, onSuccess: resetForm };

    if (editingAppointment.value) {
        // Not implementing edit now
        return;
    }

    localForm.post(route('appointments.store'), options);
};

const confirmState = reactive({
    show: false,
    title: '',
    message: '',
    confirmText: 'Confirmar',
    cancelText: 'Cancelar',
    danger: false,
    maxWidth: 'sm',
    onConfirm: null,
});

const openConfirm = ({ title, message, confirmText = 'Confirmar', cancelText = 'Cancelar', danger = false, maxWidth = 'sm', onConfirm = null }) => {
    confirmState.show = true;
    confirmState.title = title;
    confirmState.message = message;
    confirmState.confirmText = confirmText;
    confirmState.cancelText = cancelText;
    confirmState.danger = danger;
    confirmState.maxWidth = maxWidth;
    confirmState.onConfirm = onConfirm;
};

const cancelAppointment = (a) => {
    openConfirm({
        title: 'Cancelar cita',
        message: `¿Desea cancelar esta cita?`,
        confirmText: 'Cancelar',
        cancelText: 'Volver',
        danger: true,
        onConfirm: () => router.delete(route('appointments.destroy', a.appointment_id), { preserveScroll: true }),
    });
};

const updateStatus = ({ appointment, status }) => {
    openConfirm({
        title: 'Actualizar estado',
        message: `Cambiar estado a ${status}?`,
        confirmText: 'Confirmar',
        cancelText: 'Volver',
        onConfirm: () => router.patch(route('appointments.updateStatus', appointment.appointment_id), { status }, { preserveScroll: true }),
    });
};

const goTo = (url) => {
    if (!url) return;
    router.get(url, {}, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Citas" />

    <DashboardLayout>
        <div v-if="status" class="mb-5 rounded-2xl border border-emerald-300/20 bg-emerald-400/10 px-4 py-3 text-sm font-bold text-emerald-200">{{ status }}</div>

        <div class="grid gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <button type="button" class="rounded-2xl bg-white/5 px-3 py-2 text-sm font-bold text-slate-300 hover:bg-white/10" @click="showFilters = !showFilters">{{ showFilters ? 'Ocultar' : 'Filtros' }}</button>
                </div>

                <div class="flex justify-end">
                    <button type="button" class="rounded-2xl bg-gradient-to-r from-teal-400 to-cyan-400 px-4 py-2 text-sm font-black text-slate-950 shadow-lg hover:opacity-95" @click="openCreate">+ Nuevo</button>
                </div>
            </div>

            <transition name="fade">
                <div v-show="showFilters" class="rounded-2xl border border-white/10 bg-[#0f1c27] p-4">
                    <form class="grid gap-3 sm:grid-cols-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400">Doctor</label>
                            <select class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none">
                                <option value="">Todos</option>
                                <option v-for="d in props.doctors" :key="d.doctor_id" :value="d.doctor_id">{{ d.name }}</option>
                            </select>
                        </div>
                    </form>
                </div>
            </transition>

            <AppointmentTable :appointments="props.appointments.data" @cancel="cancelAppointment" @updateStatus="updateStatus" />

            <div v-if="props.appointments.links" class="flex items-center justify-between px-2 py-4">
                <div class="text-sm text-slate-400">Mostrando {{ props.appointments.from }} - {{ props.appointments.to }} de {{ props.appointments.total }}</div>
                <div class="flex items-center gap-2">
                    <button v-for="link in props.appointments.links" :key="link.label + (link.url || '')" v-html="link.label" :disabled="!link.url" @click.prevent="goTo(link.url)" class="rounded-md px-3 py-1 text-sm font-bold text-slate-300 hover:bg-white/10 disabled:opacity-40" :class="link.active ? 'bg-white/10' : ''"></button>
                </div>
            </div>

            <!-- Creación en página separada -->

            <ConfirmDialog :show="confirmState.show" :title="confirmState.title" :message="confirmState.message" :confirm-text="confirmState.confirmText" :cancel-text="confirmState.cancelText" :danger="confirmState.danger" :max-width="confirmState.maxWidth" @confirm="confirmState.onConfirm && confirmState.onConfirm()" @close="confirmState.show = false" />
        </div>
    </DashboardLayout>
</template>
