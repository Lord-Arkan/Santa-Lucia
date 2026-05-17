<script setup>
import { computed, ref, reactive } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ServiceForm from '@/Components/services/ServiceForm.vue';
import ServiceTable from '@/Components/services/ServiceTable.vue';
import DialogModal from '@/Components/DialogModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { serviceService } from '@/services/serviceService';

const props = defineProps({
    services: { type: Object, required: true },
    specialties: { type: Array, required: true },
    doctors: { type: Array, required: true },
});

const page = usePage();
const showFilters = ref(false);
const filters = ref({
    name: page.props.filters?.name ?? '',
    specialty_id: page.props.filters?.specialty_id ?? '',
});

const applyFilters = () => {
    router.get(route('services.index'), {
        name: filters.value.name || undefined,
        specialty_id: filters.value.specialty_id || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    filters.value.name = '';
    filters.value.specialty_id = '';
    router.get(route('services.index'), {}, { preserveState: true, replace: true });
};

const goTo = (url) => {
    if (!url) return;
    router.get(url, {}, { preserveState: true, replace: true });
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

const editingService = ref(null);
const showModal = ref(false);
const status = computed(() => page.props.flash?.status ?? '');

const form = useForm(serviceService.defaultForm());

const resetForm = () => {
    editingService.value = null;
    form.reset();
    form.clearErrors();
    form.status = 'activo';
    showModal.value = false;
};

const editService = (service) => {
    editingService.value = service;
    form.clearErrors();
    form.name = service.name;
    form.description = service.description;
    form.price = service.price;
    form.duration_minutes = service.duration_minutes;
    form.specialty_ids = service.specialty_ids ?? [];
    form.doctor_ids = service.doctor_ids ?? [];
    form.status = service.status ?? 'activo';
    showModal.value = true;
};

const openCreate = () => {
    editingService.value = null;
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

    if (editingService.value) {
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(route('services.update', editingService.value.service_id), {
                ...options,
                onFinish: () => form.transform((data) => data),
            });

        return;
    }

    form.post(route('services.store'), options);
};

const deleteService = (service) => {
    openConfirm({
        title: 'Eliminar servicio',
        message: `¿Eliminar ${service.name}? Esta acción no se puede deshacer.`,
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
        danger: true,
        onConfirm: () => router.delete(route('services.destroy', service.service_id), { preserveScroll: true }),
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

const toggleServiceStatus = (service) => {
    const action = service.status === 'activo' ? 'Inhabilitar' : 'Habilitar';
    const danger = service.status === 'activo';

    openConfirm({
        title: `${action} servicio`,
        message: `${action} ${service.name}?`,
        confirmText: action,
        cancelText: 'Cancelar',
        danger,
        onConfirm: () => router.patch(route('services.toggleStatus', service.service_id), {}, { preserveScroll: true }),
    });
};
</script>

<template>
    <Head title="Servicios" />

    <DashboardLayout>
        <div v-if="status" class="mb-5 rounded-2xl border border-emerald-300/20 bg-emerald-400/10 px-4 py-3 text-sm font-bold text-emerald-200">
            {{ status }}
        </div>

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
                            <label class="block text-xs font-bold text-slate-400">Especialidad</label>
                            <select v-model="filters.specialty_id" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                                <option value="">Todas</option>
                                <option v-for="s in props.specialties" :key="s.specialty_id" :value="s.specialty_id">{{ s.name }}</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2 sm:col-span-2">
                            <button type="submit" class="h-10 rounded-2xl bg-teal-400/80 px-4 text-sm font-black text-slate-950">Aplicar</button>
                            <button type="button" @click="clearFilters" class="h-10 rounded-2xl border border-white/10 px-4 text-sm font-black text-slate-300">Limpiar</button>
                        </div>
                    </form>
                </div>
            </transition>

            <ServiceTable
                :services="props.services.data"
                @edit="editService"
                @delete="deleteService"
                @toggle="toggleServiceStatus"
            />

            <div v-if="props.services.links" class="flex items-center justify-between px-2 py-4">
                <div class="text-sm text-slate-400">Mostrando {{ props.services.from }} - {{ props.services.to }} de {{ props.services.total }}</div>
                <div class="flex items-center gap-2">
                    <button
                        v-for="link in props.services.links"
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
                    <ServiceForm
                        :form="form"
                        :editing-service="editingService"
                        :specialties="props.specialties"
                        :doctors="props.doctors"
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
