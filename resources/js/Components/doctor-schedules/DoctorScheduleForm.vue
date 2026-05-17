<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    editingSchedule: { type: Object, default: null },
    doctors: { type: Array, default: () => [] },
    isDoctor: { type: Boolean, default: false },
    myDoctor: { type: Object, default: null },
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

// If user is doctor, ensure form.doctor_id is set to their doctor id
watch(
    () => [props.isDoctor, props.myDoctor],
    () => {
        if (props.isDoctor && props.myDoctor) {
            props.form.doctor_id = props.myDoctor.doctor_id;
        }
    },
    { immediate: true }
);
</script>

<template>
    <form class="relative rounded-[2rem] border border-white/10 bg-[#162130] p-5 shadow-xl shadow-slate-950/10" @submit.prevent="$emit('submit')">
        <button
            type="button"
            @click="$emit('cancel')"
            aria-label="Cerrar"
            class="absolute top-3 right-3 z-20 flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-[#0b1620] text-slate-300 shadow-md hover:bg-white/10 hover:text-white transition"
        >
            <span class="sr-only">Cerrar</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">{{ title }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
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

            <div v-if="!isDoctor && doctors && doctors.length" class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-400">Doctor</label>
                <select v-model="form.doctor_id" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                    <option value="">Seleccione</option>
                    <option v-for="d in doctors" :key="d.doctor_id" :value="d.doctor_id">{{ d.name }}</option>
                </select>
                <span v-if="form.errors.doctor_id" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.doctor_id }}</span>
            </div>

            <div v-else-if="isDoctor" class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-400">Doctor</label>
                <div class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none flex items-center">
                    <span>{{ myDoctor ? myDoctor.name : 'No registrado' }}</span>
                </div>
                <input type="hidden" v-model="form.doctor_id" />
                <span v-if="form.errors.doctor_id" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.doctor_id }}</span>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-2">
            <button type="button" class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-black text-slate-300" @click="$emit('cancel')">Cancelar</button>
            <button type="submit" :disabled="form.processing" class="rounded-2xl bg-teal-400 px-4 py-2 text-sm font-black text-slate-950" >
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </button>
        </div>
    </form>
</template>
