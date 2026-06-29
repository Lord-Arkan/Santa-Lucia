<script setup>
import RowActionMenu from '@/Components/ui/RowActionMenu.vue';

defineProps({
    patients: {
        type: Array,
        required: true,
    },
    currentUserId: {
        type: Number,
        default: null,
    },
    canManage: {
        type: Boolean,
        default: false,
    },
    canAddClinicalRecords: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['edit', 'delete', 'toggle', 'addRecord']);

const patientActions = (patient, canManage, canAddClinicalRecords) => [
    { key: 'history', label: 'Historial', tone: 'info', href: route('patients.history', patient.patient_id) },
    { key: 'addRecord', label: 'Añadir registro', tone: 'success', visible: canAddClinicalRecords },
    { key: 'records', label: 'Registros', tone: 'violet', href: route('patients.records.index', patient.patient_id) },
    { key: 'toggle', label: patient.status === 'activo' ? 'Inhabilitar' : 'Habilitar', tone: patient.status === 'activo' ? 'warning' : 'success', visible: canManage },
    { key: 'edit', label: 'Editar', visible: canManage },
    { key: 'delete', label: 'Eliminar', tone: 'danger', visible: canManage },
];

const handleAction = (patient, action) => {
    if (action.href) return;
    emit(action.key, patient);
};

</script>

<template>
    <section class="rounded-2xl border border-white/10 bg-[#162130] shadow-xl shadow-slate-950/10 sm:rounded-[2rem]">
        <div class="border-b border-white/10 p-4 sm:p-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-teal-300 sm:text-xs sm:tracking-[0.24em]">Pacientes</p>
        </div>

        <div class="grid gap-3 p-3 sm:hidden">
            <article v-for="patient in patients" :key="patient.patient_id" class="rounded-2xl border border-white/10 bg-slate-950/30 p-3">
                <div class="flex items-start gap-3">
                    <span :class="['grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-xs font-black', patient.status === 'inactivo' ? 'text-rose-200' : 'text-white']">
                        {{ (patient.first_name || '').slice(0,1).toUpperCase() }}{{ (patient.last_name || '').slice(0,1).toUpperCase() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p :class="['truncate text-sm font-black', patient.status === 'inactivo' ? 'text-rose-400' : 'text-white']">{{ patient.first_name }} {{ patient.last_name }}</p>
                        <p :class="['mt-1 truncate text-xs font-semibold', patient.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400']">{{ patient.email }}</p>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-semibold">
                    <p :class="patient.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400'"><span class="block text-slate-500">Documento</span>{{ patient.document_type }} {{ patient.document_number }}</p>
                    <p :class="patient.status === 'inactivo' ? 'text-rose-300' : 'text-slate-400'"><span class="block text-slate-500">Alta</span>{{ patient.created_at }}</p>
                </div>
                <div class="mt-3 flex justify-end">
                    <RowActionMenu :actions="patientActions(patient, canManage, canAddClinicalRecords)" @select="handleAction(patient, $event)" />
                </div>
            </article>
            <p v-if="!patients.length" class="py-6 text-center text-sm font-semibold text-slate-500">No hay pacientes registrados.</p>
        </div>

        <div class="hidden overflow-x-auto sm:block">
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
                            <div class="flex justify-end">
                                <RowActionMenu :actions="patientActions(patient, canManage, canAddClinicalRecords)" @select="handleAction(patient, $event)" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
