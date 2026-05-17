<script setup>
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
        default:
            return 'bg-white/5 text-slate-300';
    }
};
</script>

<template>
    <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10">
        <div class="border-b border-white/10 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">Citas</p>
        </div>

        <div class="overflow-x-auto">
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
                            <div class="flex justify-end gap-2">
                                <button type="button" class="rounded-xl border px-3 py-2 text-xs font-black text-sky-200 hover:bg-sky-400/10" @click="$emit('show', a)">Detalle</button>
                                <button v-if="a.status === 'SCHEDULED'" type="button" class="rounded-xl border px-3 py-2 text-xs font-black text-rose-200 hover:bg-rose-400/10" @click="$emit('cancel', a)">Cancelar</button>
                                <button v-if="a.status === 'SCHEDULED'" type="button" class="rounded-xl border px-3 py-2 text-xs font-black text-emerald-200 hover:bg-emerald-400/10" @click="$emit('updateStatus', { appointment: a, status: 'COMPLETED' })">Completar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
