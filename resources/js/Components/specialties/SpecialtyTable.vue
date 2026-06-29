<script setup>
import RowActionMenu from '@/Components/ui/RowActionMenu.vue';

const props = defineProps({
    specialties: { type: Array, required: true },
});

const emit = defineEmits(['edit', 'delete', 'toggle']);

const specialtyActions = (specialty) => [
    { key: 'toggle', label: specialty.status === 'activo' ? 'Inhabilitar' : 'Habilitar', tone: specialty.status === 'activo' ? 'warning' : 'success' },
    { key: 'edit', label: 'Editar' },
    { key: 'delete', label: 'Eliminar', tone: 'danger' },
];

const handleAction = (specialty, action) => {
    emit(action.key, specialty);
};
</script>

<template>
    <section class="rounded-2xl border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10 sm:rounded-[2rem]">
        <div class="border-b border-white/10 p-4 sm:p-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-teal-300 sm:text-xs sm:tracking-[0.24em]">Especialidades</p>
        </div>

        <div class="grid gap-3 p-3 sm:hidden">
            <article v-for="specialty in props.specialties" :key="specialty.specialty_id" class="rounded-2xl border border-white/10 bg-slate-950/30 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span :class="['grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-xs font-black', specialty.status === 'inactivo' ? 'text-rose-200' : 'text-white']">
                            {{ specialty.name.slice(0, 2).toUpperCase() }}
                        </span>
                        <p :class="['truncate text-sm font-black', specialty.status === 'inactivo' ? 'text-rose-300' : 'text-white']">{{ specialty.name }}</p>
                    </div>
                    <span :class="['shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black', specialty.status === 'activo' ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200']">
                        {{ specialty.status === 'activo' ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-semibold text-slate-400">
                    <p><span class="block text-slate-500">Creacion</span>{{ specialty.created_at }}</p>
                    <p><span class="block text-slate-500">Actualizacion</span>{{ specialty.updated_at }}</p>
                </div>
                <div class="mt-3 flex justify-end">
                    <RowActionMenu :actions="specialtyActions(specialty)" @select="handleAction(specialty, $event)" />
                </div>
            </article>
            <p v-if="!props.specialties.length" class="py-6 text-center text-sm font-semibold text-slate-500">No hay especialidades registradas.</p>
        </div>

        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-[760px] w-full text-left">
                <thead class="bg-slate-950/30 text-xs uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-black">Especialidad</th>
                        <th class="px-5 py-4 font-black">Estado</th>
                        <th class="px-5 py-4 font-black">Creacion</th>
                        <th class="px-5 py-4 font-black">Actualizacion</th>
                        <th class="px-5 py-4 text-right font-black">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr v-for="specialty in props.specialties" :key="specialty.specialty_id" class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span :class="['grid size-11 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-sm font-black', specialty.status === 'inactivo' ? 'text-rose-200' : 'text-white']">
                                    {{ specialty.name.slice(0, 2).toUpperCase() }}
                                </span>
                                <span :class="['text-sm font-black', specialty.status === 'inactivo' ? 'text-rose-300' : 'text-white']">{{ specialty.name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['rounded-full px-3 py-1.5 text-xs font-black', specialty.status === 'activo' ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200']">
                                {{ specialty.status === 'activo' ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-400">{{ specialty.created_at }}</td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-400">{{ specialty.updated_at }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end">
                                <RowActionMenu :actions="specialtyActions(specialty)" @select="handleAction(specialty, $event)" />
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!props.specialties.length">
                        <td colspan="5" class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No hay especialidades registradas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
