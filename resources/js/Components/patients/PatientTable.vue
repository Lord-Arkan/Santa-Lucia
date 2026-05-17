<script setup>
import { patientService } from '@/services/patientService';

defineProps({
    patients: {
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
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">Pacientes</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[820px] w-full text-left">
                <thead class="bg-slate-950/30 text-xs uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-black">Paciente</th>
                        <th class="px-5 py-4 font-black">Documento</th>
                        <th class="px-5 py-4 font-black">Alta</th>
                        <th class="px-5 py-4 text-right font-black">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr v-for="patient in patients" :key="patient.patient_id" class="transition hover:bg-white/[0.03]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span :class="['grid size-11 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-sm font-black', patient.status === 'inactivo' ? 'text-rose-200' : 'text-white']">
                                    {{ (patient.first_name || '').slice(0,1).toUpperCase() }}{{ (patient.last_name || '').slice(0,1).toUpperCase() }}
                                </span>
                                <span>
                                    <span :class="['block text-sm font-black', patient.status === 'inactivo' ? 'text-rose-400' : 'text-white']">{{ patient.first_name }} {{ patient.last_name }}</span>
                                    <span :class="['block text-sm', patient.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400']">{{ patient.email }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span :class="['text-sm font-semibold', patient.status === 'inactivo' ? 'text-rose-300' : 'text-slate-300']">{{ patient.document_type }} {{ patient.document_number }}</span>
                        </td>
                        <td :class="['px-5 py-4 text-sm font-semibold', patient.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400']">{{ patient.created_at }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                
                                <button
                                    type="button"
                                    :class="[
                                        'rounded-xl border px-3 py-2 text-xs font-black transition',
                                        patient.status === 'activo'
                                            ? 'border-amber-300/20 text-amber-200 hover:bg-amber-400/10'
                                            : 'border-emerald-300/20 text-emerald-200 hover:bg-emerald-400/10'
                                    ]"
                                    @click="$emit('toggle', patient)"
                                >
                                    {{ patient.status === 'activo' ? 'Inhabilitar' : 'Habilitar' }}
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border border-white/10 px-3 py-2 text-xs font-black text-slate-300 transition hover:bg-white/10 hover:text-white"
                                    @click="$emit('edit', patient)"
                                >
                                    Editar
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl border border-rose-300/20 px-3 py-2 text-xs font-black text-rose-200 transition hover:bg-rose-400/10"
                                    @click="$emit('delete', patient)"
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
