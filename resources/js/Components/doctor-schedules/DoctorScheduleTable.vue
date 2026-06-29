<script setup>
import RowActionMenu from '@/Components/ui/RowActionMenu.vue';

const props = defineProps({
    schedules: { type: Array, required: true },
    isDoctorView: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'delete', 'toggle']);

const scheduleActions = (schedule) => [
    { key: 'edit', label: 'Editar', visible: !schedule.locked },
    { key: 'toggle', label: schedule.status === 'activo' ? 'Inhabilitar' : 'Habilitar', tone: schedule.status === 'activo' ? 'danger' : 'success', visible: !schedule.locked },
    { key: 'delete', label: 'Eliminar', tone: 'danger', visible: !schedule.locked },
];

const handleAction = (schedule, action) => {
    emit(action.key, schedule);
};
</script>

<template>
    <section class="rounded-2xl border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10 sm:rounded-[2rem]">
        <div class="border-b border-white/10 p-4 sm:p-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-teal-300 sm:text-xs sm:tracking-[0.24em]">Horarios</p>
        </div>

        <div class="grid gap-3 p-3 sm:hidden">
            <article v-for="s in schedules" :key="s.schedule_id" class="rounded-2xl border border-white/10 bg-slate-950/30 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-white">{{ s.day_of_week }}</p>
                        <p v-if="!isDoctorView" class="mt-1 truncate text-xs font-semibold text-slate-400">{{ s.doctor_name }}</p>
                    </div>
                    <span :class="['shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black', s.status === 'activo' ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200']">{{ s.status === 'activo' ? 'Activo' : 'Inactivo' }}</span>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2 text-xs font-semibold text-slate-400">
                    <p><span class="block text-slate-500">Inicio</span>{{ s.start_time }}</p>
                    <p><span class="block text-slate-500">Fin</span>{{ s.end_time }}</p>
                    <p><span class="block text-slate-500">Alta</span>{{ s.created_at }}</p>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-end gap-2">
                    <RowActionMenu v-if="!s.locked" :actions="scheduleActions(s)" @select="handleAction(s, $event)" />
                    <span v-if="s.locked" class="text-[11px] font-semibold text-rose-300">Tiene citas pendientes</span>
                </div>
            </article>
            <p v-if="!schedules.length" class="py-6 text-center text-sm font-semibold text-slate-500">No hay horarios registrados.</p>
        </div>

        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-[820px] w-full text-left">
                <thead class="bg-slate-950/30 text-xs uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-black">Día</th>
                        <th class="px-5 py-4 font-black">Inicio</th>
                        <th class="px-5 py-4 font-black">Fin</th>
                        <th v-if="!isDoctorView" class="px-5 py-4 font-black">Doctor</th>
                        <th class="px-5 py-4 font-black">Estado</th>
                        <th class="px-5 py-4 font-black">Alta</th>
                        <th class="px-5 py-4 text-right font-black">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr v-for="s in schedules" :key="s.schedule_id" class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4">
                            <span class="block text-sm font-black text-white">{{ s.day_of_week }}</span>
                        </td>
                        <td class="px-5 py-4"><span class="text-sm text-slate-400">{{ s.start_time }}</span></td>
                        <td class="px-5 py-4"><span class="text-sm text-slate-400">{{ s.end_time }}</span></td>
                        <td v-if="!isDoctorView" class="px-5 py-4"><span class="text-sm text-slate-400">{{ s.doctor_name }}</span></td>
                        <td class="px-5 py-4"><span :class="['rounded-full px-3 py-1.5 text-xs font-black', s.status === 'activo' ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200']">{{ s.status === 'activo' ? 'Activo' : 'Inactivo' }}</span></td>
                        <td class="px-5 py-4 text-sm font-semibold text-slate-400">{{ s.created_at }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end">
                                <RowActionMenu v-if="!s.locked" :actions="scheduleActions(s)" @select="handleAction(s, $event)" />
                                <template v-if="s.locked">
                                    <span class="ml-2 text-[11px] font-semibold text-rose-300">Tiene citas pendientes</span>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
