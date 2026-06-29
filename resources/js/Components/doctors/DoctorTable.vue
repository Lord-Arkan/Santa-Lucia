<script setup>
import RowActionMenu from '@/Components/ui/RowActionMenu.vue';

const props = defineProps({
    doctors: { type: Array, required: true },
});

const emit = defineEmits(['edit', 'delete', 'toggle']);

const doctorActions = (doctor) => [
    { key: 'toggle', label: doctor.status === 'activo' ? 'Inhabilitar' : 'Habilitar', tone: doctor.status === 'activo' ? 'warning' : 'success' },
    { key: 'edit', label: 'Editar' },
    { key: 'delete', label: 'Eliminar', tone: 'danger' },
];

const handleAction = (doctor, action) => {
    emit(action.key, doctor);
};
</script>

<template>
    <section class="rounded-2xl border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10 sm:rounded-[2rem]">
        <div class="border-b border-white/10 p-4 sm:p-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-teal-300 sm:text-xs sm:tracking-[0.24em]">Doctores</p>
        </div>

        <div class="grid gap-3 p-3 sm:hidden">
            <article v-for="doctor in doctors" :key="doctor.doctor_id" class="rounded-2xl border border-white/10 bg-slate-950/30 p-3">
                <div class="flex items-start gap-3">
                    <span :class="['grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-xs font-black', doctor.status === 'inactivo' ? 'text-rose-200' : 'text-white']">
                        {{ (doctor.user?.name || '').slice(0,1).toUpperCase() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p :class="['truncate text-sm font-black', doctor.status === 'inactivo' ? 'text-rose-400' : 'text-white']">{{ doctor.user?.name }}</p>
                        <p :class="['mt-1 truncate text-xs font-semibold', doctor.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400']">{{ doctor.specialty }}</p>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-semibold">
                    <p :class="doctor.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400'"><span class="block text-slate-500">Licencia</span>{{ doctor.license_number }}</p>
                    <p :class="doctor.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400'"><span class="block text-slate-500">Alta</span>{{ doctor.created_at }}</p>
                </div>
                <div class="mt-3 flex justify-end">
                    <RowActionMenu :actions="doctorActions(doctor)" @select="handleAction(doctor, $event)" />
                </div>
            </article>
            <p v-if="!doctors.length" class="py-6 text-center text-sm font-semibold text-slate-500">No hay doctores registrados.</p>
        </div>

        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-[820px] w-full text-left">
                <thead class="bg-slate-950/30 text-xs uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-black">Doctor</th>
                        <th class="px-5 py-4 font-black">Especialidad</th>
                        <th class="px-5 py-4 font-black">Licencia</th>
                        <th class="px-5 py-4 text-right font-black">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr v-for="doctor in doctors" :key="doctor.doctor_id" class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span :class="['grid size-11 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-sm font-black', doctor.status === 'inactivo' ? 'text-rose-200' : 'text-white']">
                                    {{ (doctor.user?.name || '').slice(0,1).toUpperCase() }}
                                </span>
                                <span>
                                    <span :class="['block text-sm font-black', doctor.status === 'inactivo' ? 'text-rose-400' : 'text-white']">{{ doctor.user?.name }}</span>
                                    <span :class="['block text-sm', doctor.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400']">{{ doctor.created_at }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['text-sm font-semibold', doctor.status === 'inactivo' ? 'text-rose-300' : 'text-slate-300']">{{ doctor.specialty }}</span>
                        </td>
                        <td :class="['px-5 py-4 text-sm font-semibold', doctor.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400']">{{ doctor.license_number }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end">
                                <RowActionMenu :actions="doctorActions(doctor)" @select="handleAction(doctor, $event)" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
