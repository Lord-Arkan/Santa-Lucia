<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import UserForm from '@/Components/users/UserForm.vue';
import UserTable from '@/Components/users/UserTable.vue';
import DialogModal from '@/Components/DialogModal.vue';
import { userService } from '@/services/userService';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
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
    router.get(route('usuarios.index'), {
        name: filters.value.name || undefined,
        email: filters.value.email || undefined,
        rol: filters.value.rol || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    filters.value.name = '';
    filters.value.email = '';
    filters.value.rol = '';
    router.get(route('usuarios.index'), {}, { preserveState: true, replace: true });
};

const goTo = (url) => {
    if (!url) return;
    router.get(url, {}, { preserveState: true, replace: true });
};

const translateLabel = (label) => {
    if (!label) return '';
    return String(label).replace(/Previous/g, 'Anterior').replace(/Next/g, 'Siguiente');
};
const editingUser = ref(null);
const showModal = ref(false);
const status = computed(() => page.props.flash?.status ?? '');
const currentUserId = computed(() => page.props.auth?.user?.id ?? null);

const form = useForm(userService.defaultForm());

const resetForm = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    form.rol = 'asistente';
    form.image = null;
    showModal.value = false;
};

const editUser = (user) => {
    editingUser.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.rol = user.rol;
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
            .post(route('usuarios.update', editingUser.value.id), {
                ...options,
                onFinish: () => form.transform((data) => data),
            });

        return;
    }

    form.transform((data) => data).post(route('usuarios.store'), options);
};

const deleteUser = (user) => {
    if (user.id === currentUserId.value) {
        return;
    }

    if (! window.confirm(`Eliminar a ${user.name}?`)) {
        return;
    }

    router.delete(route('usuarios.destroy', user.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Usuarios" />

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
            />

            <div v-if="props.users.links" class="flex items-center justify-between px-2 py-4">
                <div class="text-sm text-slate-400">Mostrando {{ props.users.from }} - {{ props.users.to }} de {{ props.users.total }}</div>
                <div class="flex items-center gap-2">
                    <button
                        v-for="link in props.users.links"
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
                    <UserForm
                        :form="form"
                        :roles="props.roles"
                        :editing-user="editingUser"
                        @submit="submit"
                        @cancel="resetForm"
                    />
                </template>
            </DialogModal>
        </div>
    </DashboardLayout>
</template>
