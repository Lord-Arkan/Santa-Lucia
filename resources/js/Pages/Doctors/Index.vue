<script setup>
import { computed, ref, reactive } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DoctorForm from '@/Components/doctors/DoctorForm.vue';
import DoctorTable from '@/Components/doctors/DoctorTable.vue';
import DialogModal from '@/Components/DialogModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { doctorService } from '@/services/doctorService';
import { toRelativeUrl } from '@/utils/navigation';

const props = defineProps({
    doctors: { type: Object, required: true },
    specialties: { type: Array, required: true },
    users: { type: Array, required: true },
});

const page = usePage();
const showFilters = ref(false);
const filters = ref({
    name: page.props.filters?.name ?? '',
    license_number: page.props.filters?.license_number ?? '',
});

const applyFilters = () => {
    router.get(route('doctors.index'), {
        name: filters.value.name || undefined,
        license_number: filters.value.license_number || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    filters.value.name = '';
    filters.value.license_number = '';
    router.get(route('doctors.index'), {}, { preserveState: true, replace: true });
};

const goTo = (url) => {
    if (!url) return;
    router.get(toRelativeUrl(url), {}, { preserveState: true, replace: true });
};

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

const editingDoctor = ref(null);
const showModal = ref(false);
const status = computed(() => page.props.flash?.status ?? '');

const form = useForm(doctorService.defaultForm());

const resetForm = () => {
    editingDoctor.value = null;
    form.reset();
    form.clearErrors();
    form.status = 'activo';
    showModal.value = false;
};

const editDoctor = (doctor) => {
    editingDoctor.value = doctor;
    form.clearErrors();
    form.user_id = doctor.user?.id ?? null;
    form.specialty_id = doctor.specialty_id;
    form.license_number = doctor.license_number;
    form.status = doctor.status ?? 'activo';
    showModal.value = true;
};

const openCreate = () => {
    editingDoctor.value = null;
    form.reset();
    form.clearErrors();
    form.status = 'activo';
    showModal.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: resetForm,
    };

    if (editingDoctor.value) {
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(route('doctors.update', editingDoctor.value.doctor_id), {
                ...options,
                onFinish: () => form.transform((data) => data),
            });

        return;
    }

    form.post(route('doctors.store'), options);
};

const deleteDoctor = (doctor) => {
    openConfirm({
        title: 'Eliminar doctor',
        message: `¿Eliminar a ${doctor.user?.name ?? 'este doctor'}? Esta acción no se puede deshacer.`,
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
        danger: true,
        onConfirm: () => router.delete(route('doctors.destroy', doctor.doctor_id), { preserveScroll: true }),
    });
};

const confirmState = reactive({
    show: false,
    title: '',
    message: '',
    confirmText: 'Aceptar',
    cancelText: 'Cancelar',
    onConfirm: null,
    danger: false,
    maxWidth: 'sm',
});

const openConfirm = ({ title, message, confirmText = 'Aceptar', cancelText = 'Cancelar', onConfirm = null, danger = false, maxWidth = 'sm' }) => {
    confirmState.title = title;
    confirmState.message = message;
    confirmState.confirmText = confirmText;
    confirmState.cancelText = cancelText;
    confirmState.onConfirm = onConfirm;
    confirmState.danger = danger;
    confirmState.maxWidth = maxWidth;
    confirmState.show = true;
};

const toggleDoctorStatus = (doctor) => {
    const action = doctor.status === 'activo' ? 'Inhabilitar' : 'Habilitar';
    const danger = doctor.status === 'activo';

    openConfirm({
        title: `${action} doctor`,
        message: `${action} a ${doctor.user?.name ?? 'este doctor'}?`,
        confirmText: action,
        cancelText: 'Cancelar',
        danger,
        onConfirm: () => router.patch(route('doctors.toggleStatus', doctor.doctor_id), {}, { preserveScroll: true }),
    });
};
</script>

<template>
    <Head title="Doctores" />

    <DashboardLayout>
        <!-- status flash moved to global toast Banner component -->

        <div class="grid gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <button
                        type="button"
                        class="rounded-2xl bg-white/5 px-3 py-2 text-sm font-bold text-slate-300 hover:bg-white/10"
                        @click="showFilters = !showFilters"
                    >
                        {{ showFilters ? 'Ocultar' : 'Filtros' }}
                    </button>
                </div>

                <div class="flex justify-end">
                    <button
                        type="button"
                        class="rounded-2xl bg-gradient-to-r from-teal-400 to-cyan-400 px-4 py-2 text-sm font-black text-slate-950 shadow-lg hover:opacity-95"
                        @click="openCreate"
                    >
                        + Nuevo
                    </button>
                </div>
            </div>

            <transition name="fade">
                <div v-show="showFilters" class="rounded-2xl border border-white/10 bg-[#0f1c27] p-4">
                    <form class="grid gap-3 sm:grid-cols-4" @submit.prevent="applyFilters">
                        <div>
                            <label class="block text-xs font-bold text-slate-400">Nombre</label>
                            <input v-model="filters.name" type="text" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Nombre" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400">Licencia</label>
                            <input v-model="filters.license_number" type="text" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Número de licencia" />
                        </div>
                        <div class="flex items-end gap-2 sm:col-span-2">
                            <button type="submit" class="h-10 rounded-2xl bg-teal-400/80 px-4 text-sm font-black text-slate-950">Aplicar</button>
                            <button type="button" @click="clearFilters" class="h-10 rounded-2xl border border-white/10 px-4 text-sm font-black text-slate-300">Limpiar</button>
                        </div>
                    </form>
                </div>
            </transition>

            <DoctorTable
                :doctors="props.doctors.data"
                @edit="editDoctor"
                @delete="deleteDoctor"
                @toggle="toggleDoctorStatus"
            />

            <div v-if="props.doctors.links" class="flex items-center justify-between px-2 py-4">
                <div class="text-sm text-slate-400">Mostrando {{ props.doctors.from }} - {{ props.doctors.to }} de {{ props.doctors.total }}</div>
                <div class="flex items-center gap-2">
                    <button
                        v-for="link in props.doctors.links"
                        :key="link.label + (link.url || '')"
                        v-html="translateLabel(link.label)"
                        :disabled="!link.url"
                        @click.prevent="goTo(link.url)"
                        class="rounded-md px-3 py-1 text-sm font-bold text-slate-300 hover:bg-white/10 disabled:opacity-40"
                        :class="link.active ? 'bg-white/10' : ''"
                    ></button>
                </div>
            </div>

            <DialogModal :show="showModal" @close="showModal = false" max-width="2xl">
                <template #content>
                    <DoctorForm
                        :form="form"
                        :editing-doctor="editingDoctor"
                        :users="props.users"
                        :specialties="props.specialties"
                        @submit="submit"
                        @cancel="resetForm"
                    />
                </template>
            </DialogModal>

            <ConfirmDialog
                :show="confirmState.show"
                :title="confirmState.title"
                :message="confirmState.message"
                :confirm-text="confirmState.confirmText"
                :cancel-text="confirmState.cancelText"
                :danger="confirmState.danger"
                :max-width="confirmState.maxWidth"
                @confirm="confirmState.onConfirm && confirmState.onConfirm()"
                @close="confirmState.show = false"
            />
        </div>
    </DashboardLayout>
</template>
