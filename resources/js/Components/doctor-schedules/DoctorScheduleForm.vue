<script setup>
import { computed } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    editingSchedule: { type: Object, default: null },
    doctors: { type: Array, default: () => [] },
});

const emit = defineEmits(['submit', 'cancel']);

const days = [
    { value: 'MONDAY', label: 'Lunes' },
    { value: 'TUESDAY', label: 'Martes' },
    { value: 'WEDNESDAY', label: 'Miércoles' },
    { value: 'THURSDAY', label: 'Jueves' },
    { value: 'FRIDAY', label: 'Viernes' },
    { value: 'SATURDAY', label: 'Sábado' },
    { value: 'SUNDAY', label: 'Domingo' },
];

const title = computed(() => (props.editingSchedule ? 'Editar horario' : 'Nuevo horario'));
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-400">Día</label>
            <select v-model="form.day_of_week" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                <option value="">Seleccione</option>
                <option v-for="d in days" :key="d.value" :value="d.value">{{ d.label }}</option>
            </select>
            <span v-if="form.errors.day_of_week" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.day_of_week }}</span>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-400">Hora inicio</label>
            <input v-model="form.start_time" type="time" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" />
            <span v-if="form.errors.start_time" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.start_time }}</span>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-400">Hora fin</label>
            <input v-model="form.end_time" type="time" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" />
            <span v-if="form.errors.end_time" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.end_time }}</span>
        </div>

        <div v-if="doctors && doctors.length" class="sm:col-span-2">
            <label class="block text-xs font-bold text-slate-400">Doctor</label>
            <select v-model="form.doctor_id" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                <option value="">Seleccione</option>
                <option v-for="d in doctors" :key="d.doctor_id" :value="d.doctor_id">{{ d.name }}</option>
            </select>
            <span v-if="form.errors.doctor_id" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.doctor_id }}</span>
        </div>

        <div class="sm:col-span-2 flex items-center justify-end gap-2">
            <button type="button" class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-black text-slate-300" @click="$emit('cancel')">Cancelar</button>
            <button type="button" class="rounded-2xl bg-teal-400 px-4 py-2 text-sm font-black text-slate-950" @click="$emit('submit')">Guardar</button>
        </div>
    </div>
</template>
