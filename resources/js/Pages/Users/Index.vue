<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import UserForm from '@/Components/users/UserForm.vue';
import UserTable from '@/Components/users/UserTable.vue';
import DialogModal from '@/Components/DialogModal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { userService } from '@/services/userService';
import { toRelativeUrl } from '@/utils/navigation';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    modules: {
        type: Array,
        required: true,
    },
    moduleDefaults: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const showFilters = ref(false);
const filters = ref({
    name: page.props.filters?.name ?? '',
    email: page.props.filters?.email ?? '',
    rol: page.props.filters?.rol ?? '',
});

const applyFilters = () => {
    router.get(route('users.index'), {
        name: filters.value.name || undefined,
        email: filters.value.email || undefined,
        rol: filters.value.rol || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    filters.value.name = '';
    filters.value.email = '';
    filters.value.rol = '';
    router.get(route('users.index'), {}, { preserveState: true, replace: true });
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
const editingUser = ref(null);
const showModal = ref(false);
const status = computed(() => page.props.flash?.status ?? '');
const currentUserId = computed(() => page.props.auth?.user?.id ?? null);

const form = useForm(userService.defaultForm(props.moduleDefaults.asistente));

const resetForm = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    form.rol = 'asistente';
    form.module_permissions = [...props.moduleDefaults.asistente];
    form.image = null;
    showModal.value = false;
};

const editUser = (user) => {
    editingUser.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.rol = user.rol;
    form.module_permissions = [...user.module_permissions];
    form.password = '';
    form.password_confirmation = '';
    form.image = null;
    showModal.value = true;
};

const openCreate = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    form.rol = 'asistente';
    form.module_permissions = [...props.moduleDefaults.asistente];
    form.image = null;
    showModal.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: resetForm,
    };

    if (editingUser.value) {
        form
            .transform((data) => ({
                ...data,
                _method: 'put',
            }))
            .post(route('users.update', editingUser.value.id), {
                ...options,
                onFinish: () => form.transform((data) => data),
            });

        return;
    }

    form.transform((data) => data).post(route('users.store'), options);
};

const confirmState = ref({
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
    confirmState.value = { show: true, title, message, confirmText, cancelText, danger, maxWidth, onConfirm };
};

const deleteUser = (user) => {
    if (user.id === currentUserId.value) return;

    openConfirm({
        title: 'Eliminar usuario',
        message: `Eliminar a ${user.name}?`,
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
        danger: true,
        maxWidth: 'sm',
        onConfirm: () => router.delete(route('users.destroy', user.id), { preserveScroll: true }),
    });
};

const toggleUserStatus = (user) => {
    const action = user.status ? 'Deshabilitar' : 'Habilitar';
    const danger = user.status;

    openConfirm({
        title: `${action} usuario`,
        message: `${action} a ${user.name}?`,
        confirmText: action,
        cancelText: 'Cancelar',
        danger,
        onConfirm: () => router.patch(route('users.toggleStatus', user.id), {}, { preserveScroll: true }),
    });
};
</script>

<template>
    <Head title="Usuarios" />

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

                <div class="flex justify-end">
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
                            <label class="block text-xs font-bold text-slate-400">Nombre</label>
                            <input v-model="filters.name" type="text" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Nombre" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400">Correo</label>
                            <input v-model="filters.email" type="text" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Correo" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400">Rol</label>
                            <select v-model="filters.rol" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                                <option value="">Todos</option>
                                <option v-for="role in props.roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="h-10 rounded-2xl bg-teal-400/80 px-4 text-sm font-black text-slate-950">Aplicar</button>
                            <button type="button" @click="clearFilters" class="h-10 rounded-2xl border border-white/10 px-4 text-sm font-black text-slate-300">Limpiar</button>
                        </div>
                    </form>
                </div>
            </transition>

            <UserTable
                :users="props.users.data"
                :current-user-id="currentUserId"
                @edit="editUser"
                @delete="deleteUser"
                @toggle="toggleUserStatus"
            />

            <div v-if="props.users.links" class="flex flex-col items-start gap-3 px-1 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-2 sm:py-4">
                <div class="text-sm text-slate-400">Mostrando {{ props.users.from }} - {{ props.users.to }} de {{ props.users.total }}</div>
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <button
                        v-for="link in props.users.links"
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
                    <UserForm
                        :form="form"
                        :roles="props.roles"
                        :modules="props.modules"
                        :editing-user="editingUser"
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
