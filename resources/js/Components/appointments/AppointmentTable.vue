<script setup>
import RowActionMenu from '@/Components/ui/RowActionMenu.vue';

const props = defineProps({
    appointments: { type: Array, required: true },
});

const emit = defineEmits(['cancel', 'updateStatus', 'show']);

const statusClass = (status) => {
    if (!status) return 'bg-white/5 text-slate-300';
    switch (status.toString().toUpperCase()) {
        case 'SCHEDULED':
            return 'bg-emerald-300/10 text-emerald-200';
        case 'COMPLETED':
        case 'COMPLETED_A':
        case 'COMPLETADA':
            return 'bg-sky-300/10 text-sky-200';
        case 'CANCELLED':
        case 'CANCELED':
        case 'CANCELADA':
            return 'bg-rose-300/10 text-rose-200';
        case 'EXPIRED':
            return 'bg-amber-300/10 text-amber-200';
        default:
            return 'bg-white/5 text-slate-300';
    }
};

const appointmentActions = (appointment) => [
    { key: 'show', label: 'Detalle', tone: 'info' },
    { key: 'cancel', label: 'Cancelar', tone: 'danger', visible: appointment.status === 'SCHEDULED' },
    { key: 'complete', label: 'Completar', tone: 'success', visible: appointment.status === 'SCHEDULED' },
];

const handleAction = (appointment, action) => {
    if (action.key === 'show') emit('show', appointment);
    if (action.key === 'cancel') emit('cancel', appointment);
    if (action.key === 'complete') emit('updateStatus', { appointment, status: 'COMPLETED' });
};
</script>

<template>
    <section class="rounded-2xl border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10 sm:rounded-[2rem]">
        <div class="border-b border-white/10 p-4 sm:p-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-teal-300 sm:text-xs sm:tracking-[0.24em]">Citas</p>
        </div>

        <div class="grid gap-3 p-3 sm:hidden">
            <article v-for="a in props.appointments" :key="a.appointment_id" class="rounded-2xl border border-white/10 bg-slate-950/30 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-white">{{ a.patient?.name }}</p>
                        <p class="mt-1 truncate text-xs font-semibold text-slate-400">{{ a.doctor?.name }}</p>
                    </div>
                    <span :class="['shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black', statusClass(a.status)]">{{ a.status_label ?? a.status }}</span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-semibold text-slate-400">
                    <p><span class="block text-slate-500">Servicio</span>{{ a.service?.name }}</p>
                    <p><span class="block text-slate-500">Fecha</span>{{ a.appointment_date }}</p>
                    <p><span class="block text-slate-500">Inicio</span>{{ a.start_time }}</p>
                    <p><span class="block text-slate-500">Fin</span>{{ a.end_time }}</p>
                </div>
                <div class="mt-3 flex justify-end">
                    <RowActionMenu :actions="appointmentActions(a)" @select="handleAction(a, $event)" />
                </div>
            </article>
            <p v-if="!props.appointments.length" class="py-6 text-center text-sm font-semibold text-slate-500">No hay citas registradas.</p>
        </div>

        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-[820px] w-full text-left">
                <thead class="bg-slate-950/30 text-xs uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-black">Paciente</th>
                        <th class="px-5 py-4 font-black">Doctor</th>
                        <th class="px-5 py-4 font-black">Servicio</th>
                        <th class="px-5 py-4 font-black">Fecha</th>
                        <th class="px-5 py-4 font-black">Inicio</th>
                        <th class="px-5 py-4 font-black">Fin</th>
                        <th class="px-5 py-4 font-black">Estado</th>
                        <th class="px-5 py-4 text-right font-black">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr v-for="a in props.appointments" :key="a.appointment_id" class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4"><span class="text-sm text-slate-400">{{ a.patient?.name }}</span></td>
                        <td class="px-5 py-4"><span class="text-sm text-slate-400">{{ a.doctor?.name }}</span></td>
                        <td class="px-5 py-4"><span class="text-sm text-slate-400">{{ a.service?.name }}</span></td>
                        <td class="px-5 py-4"><span class="text-sm text-slate-400">{{ a.appointment_date }}</span></td>
                        <td class="px-5 py-4"><span class="text-sm text-slate-400">{{ a.start_time }}</span></td>
                        <td class="px-5 py-4"><span class="text-sm text-slate-400">{{ a.end_time }}</span></td>
                        <td class="px-5 py-4"><span :class="['rounded-full px-3 py-1.5 text-xs font-black', statusClass(a.status)]">{{ a.status_label ?? a.status }}</span></td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end">
                                <RowActionMenu :actions="appointmentActions(a)" @select="handleAction(a, $event)" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
