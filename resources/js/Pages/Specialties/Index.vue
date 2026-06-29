<script setup>
import { ref, reactive } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import SpecialtyForm from '@/Components/specialties/SpecialtyForm.vue';
import SpecialtyTable from '@/Components/specialties/SpecialtyTable.vue';
import { specialtyService } from '@/services/specialtyService';
import { toRelativeUrl } from '@/utils/navigation';

const props = defineProps({
    specialties: { type: Object, required: true },
});

const page = usePage();
const showFilters = ref(false);
const showModal = ref(false);
const editingSpecialty = ref(null);
const filters = ref({
    search: page.props.filters?.search ?? '',
    status: page.props.filters?.status ?? '',
});
const form = useForm(specialtyService.defaultForm());

const translateLabel = (label) => String(label || '')
    .replace(/pagination\.previous/g, 'Anterior')
    .replace(/pagination\.next/g, 'Siguiente')
    .replace(/Previous|previous/g, 'Anterior')
    .replace(/Next|next/g, 'Siguiente');

const applyFilters = () => {
    router.get(route('specialties.index'), {
        search: filters.value.search || undefined,
        status: filters.value.status || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    filters.value.search = '';
    filters.value.status = '';
    router.get(route('specialties.index'), {}, { preserveState: true, replace: true });
};

const goTo = (url) => {
    if (!url) return;
    router.get(toRelativeUrl(url), {}, { preserveState: true, replace: true });
};

const resetForm = () => {
    editingSpecialty.value = null;
    form.reset();
    form.clearErrors();
    form.status = 'activo';
    showModal.value = false;
};

const openCreate = () => {
    editingSpecialty.value = null;
    form.reset();
    form.clearErrors();
    form.status = 'activo';
    showModal.value = true;
};

const editSpecialty = (specialty) => {
    editingSpecialty.value = specialty;
    form.clearErrors();
    form.name = specialty.name;
    form.status = specialty.status;
    showModal.value = true;
};

const submit = () => {
    const options = { preserveScroll: true, onSuccess: resetForm };

    if (editingSpecialty.value) {
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(route('specialties.update', editingSpecialty.value.specialty_id), {
                ...options,
                onFinish: () => form.transform((data) => data),
            });
        return;
    }

    form.post(route('specialties.store'), options);
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

const toggleSpecialtyStatus = (specialty) => {
    const action = specialty.status === 'activo' ? 'Inhabilitar' : 'Habilitar';
    openConfirm({
        title: `${action} especialidad`,
        message: `${action} ${specialty.name}?`,
        confirmText: action,
        danger: specialty.status === 'activo',
        onConfirm: () => router.patch(route('specialties.toggleStatus', specialty.specialty_id), {}, { preserveScroll: true }),
    });
};

const deleteSpecialty = (specialty) => {
    openConfirm({
        title: 'Eliminar especialidad',
        message: `Eliminar ${specialty.name}? Esta accion no se puede deshacer.`,
        confirmText: 'Eliminar',
        danger: true,
        onConfirm: () => router.delete(route('specialties.destroy', specialty.specialty_id), { preserveScroll: true }),
    });
};
</script>

<template>
    <Head title="Especialidades" />

    <DashboardLayout>
        <div class="grid gap-4 sm:gap-6">
            <div class="flex items-center justify-between">
                <button type="button" class="rounded-xl bg-white/5 px-3 py-2 text-xs font-bold text-slate-300 hover:bg-white/10 sm:rounded-2xl sm:text-sm" @click="showFilters = !showFilters">
                    {{ showFilters ? 'Ocultar' : 'Filtros' }}
                </button>

                <button type="button" class="rounded-xl bg-gradient-to-r from-teal-400 to-cyan-400 px-3 py-2 text-xs font-black text-slate-950 shadow-lg hover:opacity-95 sm:rounded-2xl sm:px-4 sm:text-sm" @click="openCreate">
                    + Nuevo
                </button>
            </div>

            <transition name="fade">
                <div v-show="showFilters" class="rounded-2xl border border-white/10 bg-[#0f1c27] p-4">
                    <form class="grid gap-3 sm:grid-cols-4" @submit.prevent="applyFilters">
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Buscar</span>
                            <input v-model="filters.search" type="search" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Nombre">
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Estado</span>
                            <select v-model="filters.status" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </label>
                        <div class="flex items-end gap-2 sm:col-span-2">
                            <button type="submit" class="h-10 rounded-2xl bg-teal-400/80 px-4 text-sm font-black text-slate-950">Aplicar</button>
                            <button type="button" class="h-10 rounded-2xl border border-white/10 px-4 text-sm font-black text-slate-300" @click="clearFilters">Limpiar</button>
                        </div>
                    </form>
                </div>
            </transition>

            <SpecialtyTable :specialties="props.specialties.data" @edit="editSpecialty" @delete="deleteSpecialty" @toggle="toggleSpecialtyStatus" />

            <div v-if="props.specialties.links" class="flex flex-col items-start gap-3 px-1 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-2 sm:py-4">
                <div class="text-sm text-slate-400">Mostrando {{ props.specialties.from }} - {{ props.specialties.to }} de {{ props.specialties.total }}</div>
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <button
                        v-for="link in props.specialties.links"
                        :key="link.label + (link.url || '')"
                        v-html="translateLabel(link.label)"
                        :disabled="!link.url"
                        @click.prevent="goTo(link.url)"
                        class="rounded-md px-2.5 py-1 text-xs font-bold text-slate-300 hover:bg-white/10 disabled:opacity-40 sm:px-3 sm:text-sm"
                        :class="link.active ? 'bg-white/10' : ''"
                    ></button>
                </div>
            </div>

            <DialogModal :show="showModal" @close="showModal = false" max-width="lg">
                <template #content>
                    <SpecialtyForm :form="form" :editing-specialty="editingSpecialty" @submit="submit" @cancel="resetForm" />
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
