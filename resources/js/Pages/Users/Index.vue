<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import UserForm from '@/Components/users/UserForm.vue';
import UserTable from '@/Components/users/UserTable.vue';
import { userService } from '@/services/userService';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const editingUser = ref(null);
const status = computed(() => page.props.flash?.status ?? '');
const currentUserId = computed(() => page.props.auth?.user?.id ?? null);

const form = useForm(userService.defaultForm());

const resetForm = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    form.rol = 'asistente';
    form.image = null;
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

    <DashboardLayout title="Mantenimiento de Usuarios">
        <div v-if="status" class="mb-5 rounded-2xl border border-emerald-300/20 bg-emerald-400/10 px-4 py-3 text-sm font-bold text-emerald-200">
            {{ status }}
        </div>

        <div class="grid gap-6 xl:grid-cols-[420px_1fr]">
            <UserForm
                :form="form"
                :roles="props.roles"
                :editing-user="editingUser"
                @submit="submit"
                @cancel="resetForm"
            />

            <UserTable
                :users="props.users"
                :current-user-id="currentUserId"
                @edit="editUser"
                @delete="deleteUser"
            />
        </div>
    </DashboardLayout>
</template>
