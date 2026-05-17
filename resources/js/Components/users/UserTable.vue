<script setup>
import { userService } from '@/services/userService';

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

</script>

<template>
    <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10">
        <div class="border-b border-white/10 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">Usuarios</p>
        </div>

        <div class="overflow-x-auto">
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
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    :class="[
                                        'rounded-xl border px-3 py-2 text-xs font-black transition',
                                        user.status
                                            ? 'border-rose-300/20 text-rose-200 hover:bg-rose-400/10'
                                            : 'border-emerald-300/20 text-emerald-200 hover:bg-emerald-400/10'
                                    ]"
                                    @click="$emit('toggle', user)"
                                >
                                    {{ user.status ? 'Deshabilitar' : 'Habilitar' }}
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl border border-white/10 px-3 py-2 text-xs font-black text-slate-300 transition hover:bg-white/10 hover:text-white"
                                    @click="$emit('edit', user)"
                                >
                                    Editar
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl border border-rose-300/20 px-3 py-2 text-xs font-black text-rose-200 transition hover:bg-rose-400/10"
                                    :disabled="user.id === currentUserId"
                                    :class="user.id === currentUserId ? 'cursor-not-allowed opacity-40' : ''"
                                    @click="$emit('delete', user)"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
