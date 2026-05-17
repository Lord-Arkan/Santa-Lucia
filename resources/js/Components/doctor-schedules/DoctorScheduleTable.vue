<script setup>
const props = defineProps({
    schedules: { type: Array, required: true },
    isDoctorView: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'delete', 'toggle']);
</script>

<template>
    <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10">
        <div class="border-b border-white/10 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">Horarios</p>
        </div>

        <div class="overflow-x-auto">
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
                            <div class="flex justify-end gap-2">
                                <button type="button" class="rounded-xl border px-3 py-2 text-xs font-black text-slate-300 transition hover:bg-white/10 hover:text-white" @click="$emit('edit', s)">Editar</button>
                                <button type="button" :class="['rounded-xl border px-3 py-2 text-xs font-black transition', s.status === 'activo' ? 'border-rose-300/20 text-rose-200 hover:bg-rose-400/10' : 'border-emerald-300/20 text-emerald-200 hover:bg-emerald-400/10']" @click="$emit('toggle', s)">{{ s.status === 'activo' ? 'Inhabilitar' : 'Habilitar' }}</button>
                                <button type="button" class="rounded-xl border border-rose-300/20 px-3 py-2 text-xs font-black text-rose-200 transition hover:bg-rose-400/10" @click="$emit('delete', s)">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
