<script setup>
import { userService } from '@/services/userService';
import RowActionMenu from '@/Components/ui/RowActionMenu.vue';

defineProps({
    users: {
        type: Array,
        required: true,
    },
    currentUserId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['edit', 'delete', 'toggle']);

const userActions = (user, currentUserId) => [
    { key: 'toggle', label: user.status ? 'Deshabilitar' : 'Habilitar', tone: user.status ? 'danger' : 'success' },
    { key: 'edit', label: 'Editar' },
    { key: 'delete', label: 'Eliminar', tone: 'danger', disabled: user.id === currentUserId },
];

const handleAction = (user, action) => {
    emit(action.key, user);
};

</script>

<template>
    <section class="rounded-2xl border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10 sm:rounded-[2rem]">
        <div class="border-b border-white/10 p-4 sm:p-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-teal-300 sm:text-xs sm:tracking-[0.24em]">Usuarios</p>
        </div>

        <div class="grid gap-3 p-3 sm:hidden">
            <article v-for="user in users" :key="user.id" class="rounded-2xl border border-white/10 bg-slate-950/30 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <img v-if="user.profile_photo_path" :src="user.profile_photo_url" :alt="user.name" class="size-9 shrink-0 rounded-full object-cover ring-2 ring-teal-300/20">
                        <span v-else :class="['grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-xs font-black', user.status ? 'text-white' : 'text-rose-200']">
                            {{ user.name.slice(0, 2).toUpperCase() }}
                        </span>
                        <div class="min-w-0">
                            <p :class="['truncate text-sm font-black', user.status ? 'text-white' : 'text-rose-400']">{{ user.name }}</p>
                            <p :class="['mt-1 truncate text-xs font-semibold', user.status ? 'text-slate-400' : 'text-rose-300']">{{ user.email }}</p>
                        </div>
                    </div>
                    <span :class="['shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black', user.status ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200']">
                        {{ user.status ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-semibold">
                    <p :class="user.status ? 'text-slate-400' : 'text-rose-300'"><span class="block text-slate-500">Rol</span>{{ userService.roleLabel(user.rol) }}</p>
                    <p :class="user.status ? 'text-slate-400' : 'text-rose-300'"><span class="block text-slate-500">Alta</span>{{ user.created_at }}</p>
                </div>
                <div class="mt-3 flex justify-end">
                    <RowActionMenu :actions="userActions(user, currentUserId)" @select="handleAction(user, $event)" />
                </div>
            </article>
            <p v-if="!users.length" class="py-6 text-center text-sm font-semibold text-slate-500">No hay usuarios registrados.</p>
        </div>

        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-[820px] w-full text-left">
                <thead class="bg-slate-950/30 text-xs uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-black">Usuario</th>
                        <th class="px-5 py-4 font-black">Estado</th>
                        <th class="px-5 py-4 font-black">Rol</th>
                        <th class="px-5 py-4 font-black">Alta</th>
                        <th class="px-5 py-4 text-right font-black">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr v-for="user in users" :key="user.id" class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="user.profile_photo_path"
                                    :src="user.profile_photo_url"
                                    :alt="user.name"
                                    class="size-11 rounded-full object-cover ring-2 ring-teal-300/20"
                                >
                                <span v-else :class="['grid size-11 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-sm font-black', user.status ? 'text-white' : 'text-rose-200']">
                                    {{ user.name.slice(0, 2).toUpperCase() }}
                                </span>
                                <span>
                                    <span :class="['block text-sm font-black', user.status ? 'text-white' : 'text-rose-400']">{{ user.name }}</span>
                                    <span :class="['block text-sm', user.status ? 'text-slate-400' : 'text-rose-300']">{{ user.email }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['rounded-full px-3 py-1.5 text-xs font-black', user.status ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200']">
                                {{ user.status ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['rounded-full px-3 py-1.5 text-xs font-black', user.status ? 'bg-teal-400/10 text-teal-200' : 'bg-rose-300/10 text-rose-200']">
                                {{ userService.roleLabel(user.rol) }}
                            </span>
                        </td>
                        <td :class="['px-5 py-4 text-sm font-semibold', user.status ? 'text-slate-400' : 'text-rose-300']">{{ user.created_at }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end">
                                <RowActionMenu :actions="userActions(user, currentUserId)" @select="handleAction(user, $event)" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
