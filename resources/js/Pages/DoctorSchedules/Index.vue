<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DoctorScheduleForm from '@/Components/doctor-schedules/DoctorScheduleForm.vue';
import DoctorScheduleTable from '@/Components/doctor-schedules/DoctorScheduleTable.vue';
import DialogModal from '@/Components/DialogModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { doctorScheduleService } from '@/services/doctorScheduleService';
import { toRelativeUrl } from '@/utils/navigation';

const props = defineProps({
    doctor_schedules: { type: Object, required: true },
    doctors: { type: Array, default: () => [] },
});

const page = usePage();
const showFilters = ref(false);

const currentUser = computed(() => page.props.auth?.user ?? null);
const isDoctor = computed(() => currentUser.value && currentUser.value.rol === 'doctor');
const myDoctor = computed(() => page.props.my_doctor ?? null);

const translateLabel = (label) => {
    if (!label) return '';
    return String(label)
        .replace(/pagination\.previous/g, 'Anterior')
        .replace(/pagination\.next/g, 'Siguiente')
        .replace(/Previous/g, 'Anterior')
        .replace(/Next/g, 'Siguiente')
        .replace(/previous/g, 'Anterior')
        .replace(/next/g, 'Siguiente');
};

const editingSchedule = ref(null);
const showModal = ref(false);
const status = computed(() => page.props.flash?.status ?? '');

const form = useForm(doctorScheduleService.defaultForm());

const resetForm = () => {
    editingSchedule.value = null;
    form.reset();
    form.clearErrors();
    form.status = 'activo';
    showModal.value = false;
};

const editSchedule = (s) => {
    editingSchedule.value = s;
    form.clearErrors();
    form.doctor_id = s.doctor_id;
    form.day_of_week = s.day_of_week;
    form.start_time = s.start_time;
    form.end_time = s.end_time;
    form.status = s.status ?? 'activo';
    showModal.value = true;
};

const openCreate = () => {
    editingSchedule.value = null;
    form.reset();
    form.clearErrors();
    form.status = 'activo';

    // If current user is a doctor, auto-assign doctor_id
    if (isDoctor.value) {
        if (myDoctor.value) {
            form.doctor_id = myDoctor.value.doctor_id;
        } else {
            // no doctor record found for this user; prevent opening form
            // show error via flash or errors in page props (handled server-side)
            return;
        }
    }

    showModal.value = true;
};

const submit = () => {
    const options = { preserveScroll: true, onSuccess: resetForm };

    if (editingSchedule.value) {
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(route('doctor-schedules.update', editingSchedule.value.schedule_id), {
                ...options,
                onFinish: () => form.transform((d) => d),
            });

        return;
    }

    form.post(route('doctor-schedules.store'), options);
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

const deleteSchedule = (s) => {
    openConfirm({
        title: 'Eliminar horario',
        message: `¿Inactivar horario?`,
        confirmText: 'Inactivar',
        cancelText: 'Cancelar',
        danger: true,
        onConfirm: () => router.delete(route('doctor-schedules.destroy', s.schedule_id), { preserveScroll: true }),
    });
};

const toggleSchedule = (s) => {
    const action = s.status === 'activo' ? 'Inhabilitar' : 'Habilitar';
    const danger = s.status === 'activo';

    openConfirm({
        title: `${action} horario`,
        message: `${action} este horario?`,
        confirmText: action,
        cancelText: 'Cancelar',
        danger,
        onConfirm: () => router.patch(route('doctor-schedules.toggleStatus', s.schedule_id), {}, { preserveScroll: true }),
    });
};

const goTo = (url) => {
    if (!url) return;
    router.get(toRelativeUrl(url), {}, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Horarios" />

    <DashboardLayout>
        <!-- status flash moved to global toast Banner component -->

        <div class="grid gap-4 sm:gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <button type="button" class="rounded-xl bg-white/5 px-3 py-2 text-xs font-bold text-slate-300 hover:bg-white/10 sm:rounded-2xl sm:text-sm" @click="showFilters = !showFilters">{{ showFilters ? 'Ocultar' : 'Filtros' }}</button>
                </div>

                <div class="flex justify-end">
                    <button type="button" class="rounded-xl bg-gradient-to-r from-teal-400 to-cyan-400 px-3 py-2 text-xs font-black text-slate-950 shadow-lg hover:opacity-95 sm:rounded-2xl sm:px-4 sm:text-sm" @click="openCreate" :disabled="isDoctor && !myDoctor">+ Nuevo</button>
                </div>
            </div>

            <transition name="fade">
                <div v-show="showFilters" class="rounded-2xl border border-white/10 bg-[#0f1c27] p-4">
                    <form class="grid gap-3 sm:grid-cols-4">
                        <div v-if="!isDoctor">
                            <label class="block text-xs font-bold text-slate-400">Doctor</label>
                            <select class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                                <option value="">Todos</option>
                                <option v-for="d in props.doctors" :key="d.doctor_id" :value="d.doctor_id">{{ d.name }}</option>
                            </select>
                        </div>
                        <div v-else class="sm:col-span-4">
                            <p class="text-sm text-rose-300">Solo puedes ver y crear horarios para tu cuenta de doctor.</p>
                        </div>
                    </form>
                </div>
            </transition>

            <DoctorScheduleTable :schedules="props.doctor_schedules.data" :is-doctor-view="isDoctor" @edit="editSchedule" @delete="deleteSchedule" @toggle="toggleSchedule" />

            <div v-if="props.doctor_schedules.links" class="flex flex-col items-start gap-3 px-1 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-2 sm:py-4">
                <div class="text-sm text-slate-400">Mostrando {{ props.doctor_schedules.from }} - {{ props.doctor_schedules.to }} de {{ props.doctor_schedules.total }}</div>
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <button v-for="link in props.doctor_schedules.links" :key="link.label + (link.url || '')" v-html="translateLabel(link.label)" :disabled="!link.url" @click.prevent="goTo(link.url)" class="rounded-md px-2.5 py-1 text-xs font-bold text-slate-300 hover:bg-white/10 disabled:opacity-40 sm:px-3 sm:text-sm" :class="link.active ? 'bg-white/10' : ''"></button>
                </div>
            </div>

            <DialogModal :show="showModal" @close="showModal = false" max-width="2xl">
                <template #content>
                    <DoctorScheduleForm :form="form" :editingSchedule="editingSchedule" :doctors="props.doctors" :is-doctor="isDoctor" :my-doctor="myDoctor" @submit="submit" @cancel="resetForm" />
                </template>
            </DialogModal>

            <ConfirmDialog :show="confirmState.show" :title="confirmState.title" :message="confirmState.message" :confirm-text="confirmState.confirmText" :cancel-text="confirmState.cancelText" :danger="confirmState.danger" :max-width="confirmState.maxWidth" @confirm="confirmState.onConfirm && confirmState.onConfirm()" @close="confirmState.show = false" />
        </div>
    </DashboardLayout>
</template>
