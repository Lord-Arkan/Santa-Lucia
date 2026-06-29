<script setup>
import { computed, ref, reactive } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import PatientForm from '@/Components/patients/PatientForm.vue';
import PatientTable from '@/Components/patients/PatientTable.vue';
import DialogModal from '@/Components/DialogModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { patientService } from '@/services/patientService';
import { toRelativeUrl } from '@/utils/navigation';

const props = defineProps({
    patients: {
        type: Object,
        required: true,
    },
    canManagePatients: { type: Boolean, default: false },
    canAddClinicalRecords: { type: Boolean, default: false },
});

const page = usePage();
const showFilters = ref(false);
const filters = ref({
    first_name: page.props.filters?.first_name ?? '',
    last_name: page.props.filters?.last_name ?? '',
    document_number: page.props.filters?.document_number ?? '',
    email: page.props.filters?.email ?? '',
});

const applyFilters = () => {
    router.get(route('patients.index'), {
        first_name: filters.value.first_name || undefined,
        last_name: filters.value.last_name || undefined,
        document_number: filters.value.document_number || undefined,
        email: filters.value.email || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    filters.value.first_name = '';
    filters.value.last_name = '';
    filters.value.document_number = '';
    filters.value.email = '';
    router.get(route('patients.index'), {}, { preserveState: true, replace: true });
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

const editingPatient = ref(null);
const showModal = ref(false);
const status = computed(() => page.props.flash?.status ?? '');

const form = useForm(patientService.defaultForm());
const recordPatient = ref(null);
const showRecordModal = ref(false);
const recordForm = useForm({ type: 'Observacion', content: '' });

const openRecord = (patient) => {
    recordPatient.value = patient;
    recordForm.reset();
    recordForm.clearErrors();
    showRecordModal.value = true;
};

const submitRecord = () => {
    recordForm.post(route('patients.records.store', recordPatient.value.patient_id), {
        preserveScroll: true,
        onSuccess: () => {
            showRecordModal.value = false;
            recordPatient.value = null;
            recordForm.reset();
        },
    });
};

const resetForm = () => {
    editingPatient.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = false;
};

const editPatient = (patient) => {
    editingPatient.value = patient;
    form.clearErrors();
    form.document_type = patient.document_type;
    form.document_number = patient.document_number;
    form.first_name = patient.first_name;
    form.last_name = patient.last_name;
    form.birth_date = patient.birth_date;
    form.gender = patient.gender;
    form.phone = patient.phone;
    form.email = patient.email;
    form.address = patient.address;
    form.blood_type = patient.blood_type;
    form.allergies = patient.allergies;
    form.previous_conditions = patient.previous_conditions;
    form.insurance_type = patient.insurance_type;
    form.status = patient.status ?? 'activo';
    showModal.value = true;
};

const openCreate = () => {
    editingPatient.value = null;
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

    if (editingPatient.value) {
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(route('patients.update', editingPatient.value.patient_id), {
                ...options,
                onFinish: () => form.transform((data) => data),
            });

        return;
    }

    form.post(route('patients.store'), options);
};

const deletePatient = (patient) => {
    openConfirm({
        title: 'Eliminar paciente',
        message: `¿Eliminar a ${patient.first_name} ${patient.last_name}? Esta acción no se puede deshacer.`,
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
        danger: true,
        onConfirm: () => router.delete(route('patients.destroy', patient.patient_id), { preserveScroll: true }),
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

const togglePatientStatus = (patient) => {
    const action = patient.status === 'activo' ? 'Inhabilitar' : 'Habilitar';
    const danger = patient.status === 'activo';

    openConfirm({
        title: `${action} paciente`,
        message: `${action} a ${patient.first_name} ${patient.last_name}?`,
        confirmText: action,
        cancelText: 'Cancelar',
        danger,
        onConfirm: () => router.patch(route('patients.toggleStatus', patient.patient_id), {}, { preserveScroll: true }),
    });
};
</script>

<template>
    <Head title="Pacientes" />

    <DashboardLayout>
        <!-- status flash moved to global toast Banner component -->

        <div class="grid gap-4 sm:gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <button
                        type="button"
                        class="rounded-xl bg-white/5 px-3 py-2 text-xs font-bold text-slate-300 hover:bg-white/10 sm:rounded-2xl sm:text-sm"
                        @click="showFilters = !showFilters"
                    >
                        {{ showFilters ? 'Ocultar' : 'Filtros' }}
                    </button>
                </div>

                <div v-if="props.canManagePatients" class="flex justify-end">
                    <button
                        type="button"
                        class="rounded-xl bg-gradient-to-r from-teal-400 to-cyan-400 px-3 py-2 text-xs font-black text-slate-950 shadow-lg hover:opacity-95 sm:rounded-2xl sm:px-4 sm:text-sm"
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
                            <label class="block text-xs font-bold text-slate-400">Nombres</label>
                            <input v-model="filters.first_name" type="text" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Nombres" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400">Apellidos</label>
                            <input v-model="filters.last_name" type="text" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Apellidos" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400">Documento</label>
                            <input v-model="filters.document_number" type="text" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Documento" />
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="h-10 rounded-2xl bg-teal-400/80 px-4 text-sm font-black text-slate-950">Aplicar</button>
                            <button type="button" @click="clearFilters" class="h-10 rounded-2xl border border-white/10 px-4 text-sm font-black text-slate-300">Limpiar</button>
                        </div>
                    </form>
                </div>
            </transition>

            <PatientTable
                :patients="props.patients.data"
                :can-manage="props.canManagePatients"
                :can-add-clinical-records="props.canAddClinicalRecords"
                @edit="editPatient"
                @delete="deletePatient"
                @toggle="togglePatientStatus"
                @add-record="openRecord"
            />

            <div v-if="props.patients.links" class="flex flex-col items-start gap-3 px-1 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-2 sm:py-4">
                <div class="text-sm text-slate-400">Mostrando {{ props.patients.from }} - {{ props.patients.to }} de {{ props.patients.total }}</div>
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <button
                        v-for="link in props.patients.links"
                        :key="link.label + (link.url || '')"
                        v-html="translateLabel(link.label)"
                        :disabled="!link.url"
                        @click.prevent="goTo(link.url)"
                        class="rounded-md px-2.5 py-1 text-xs font-bold text-slate-300 hover:bg-white/10 disabled:opacity-40 sm:px-3 sm:text-sm"
                        :class="link.active ? 'bg-white/10' : ''"
                    ></button>
                </div>
            </div>

            <DialogModal :show="showModal" @close="showModal = false" max-width="2xl">
                <template #content>
                    <PatientForm
                        :form="form"
                        :editing-patient="editingPatient"
                        @submit="submit"
                        @cancel="resetForm"
                    />
                </template>
            </DialogModal>

            <DialogModal :show="showRecordModal" @close="showRecordModal = false" max-width="2xl">
                <template #content>
                    <form class="rounded-2xl border border-white/10 bg-[#162130] p-4 sm:rounded-[2rem] sm:p-6" @submit.prevent="submitRecord">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Nuevo registro clinico</p>
                        <h3 class="mt-2 text-xl font-black text-white">{{ recordPatient?.first_name }} {{ recordPatient?.last_name }}</h3>
                        <label class="mt-5 block text-xs font-bold uppercase text-slate-400">Tipo
                            <select v-model="recordForm.type" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-white">
                                <option>Receta</option><option>Observacion</option><option>Diagnostico</option><option>Indicacion</option><option>Evolucion</option><option>Otro</option>
                            </select>
                        </label>
                        <label class="mt-4 block text-xs font-bold uppercase text-slate-400">Detalle
                            <textarea v-model="recordForm.content" rows="7" class="mt-2 w-full rounded-2xl border border-white/10 bg-[#101824] p-4 text-white" />
                            <span v-if="recordForm.errors.content" class="mt-1 block text-xs font-bold text-rose-300">{{ recordForm.errors.content }}</span>
                        </label>
                        <div class="mt-5 flex justify-end gap-3">
                            <button type="button" class="rounded-xl border border-white/10 px-4 py-2 text-sm font-bold text-slate-300" @click="showRecordModal = false">Cancelar</button>
                            <button type="submit" :disabled="recordForm.processing" class="rounded-xl bg-teal-400 px-4 py-2 text-sm font-black text-slate-950">Guardar registro</button>
                        </div>
                    </form>
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
