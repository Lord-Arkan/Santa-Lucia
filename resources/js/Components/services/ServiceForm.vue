<script setup>
import { computed } from 'vue';
import MultiSelectDropdown from '@/Components/MultiSelectDropdown.vue';

const props = defineProps({
    form: { type: Object, required: true },
    editingService: { type: Object, default: null },
    specialties: { type: Array, default: () => [] },
    doctors: { type: Array, default: () => [] },
});

const emit = defineEmits(['submit', 'cancel']);
const isEditing = computed(() => Boolean(props.editingService));
</script>

<template>
    <form class="relative rounded-2xl border border-white/10 bg-[#162130] p-4 shadow-xl shadow-slate-950/10 sm:rounded-[2rem] sm:p-5" @submit.prevent="$emit('submit')">
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
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">{{ isEditing ? 'Editar servicio' : 'Nuevo servicio' }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <label class="block sm:col-span-2">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Nombre</span>
                <input v-model="form.name" type="text" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="Nombre del servicio">
                <span v-if="form.errors.name" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.name }}</span>
            </label>

            <label class="block sm:col-span-2">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Descripción</span>
                <textarea v-model="form.description" rows="3" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white outline-none transition" placeholder="Descripción (opcional)"></textarea>
                <span v-if="form.errors.description" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.description }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Precio</span>
                <input v-model="form.price" type="number" step="0.01" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="0.00">
                <span v-if="form.errors.price" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.price }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Duración (min)</span>
                <input v-model="form.duration_minutes" type="number" step="1" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="30">
                <span v-if="form.errors.duration_minutes" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.duration_minutes }}</span>
            </label>

            <label class="block sm:col-span-2">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Especialidades</span>
                <small class="block text-[11px] text-slate-500">Seleccione una o más especialidades</small>
                <MultiSelectDropdown
                    :options="specialties"
                    v-model="form.specialty_ids"
                    value-key="specialty_id"
                    label-key="name"
                    placeholder="Seleccionar especialidades"
                />
                <span v-if="form.errors.specialty_ids" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.specialty_ids }}</span>
            </label>

            <label class="block sm:col-span-2">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Doctores</span>
                <small class="block text-[11px] text-slate-500">Asignar doctores (opcional)</small>
                <MultiSelectDropdown
                    :options="doctors"
                    v-model="form.doctor_ids"
                    value-key="doctor_id"
                    label-key="name"
                    placeholder="Seleccionar doctores"
                />
                <span v-if="form.errors.doctor_ids" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.doctor_ids }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Estado</span>
                <select v-model="form.status" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-sm font-semibold text-white outline-none transition">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </label>
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="mt-6 h-12 w-full rounded-2xl bg-gradient-to-r from-teal-400 to-cyan-400 px-5 text-sm font-black text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-70"
        >
            {{ form.processing ? 'Guardando...' : (isEditing ? 'Actualizar servicio' : 'Crear servicio') }}
        </button>
    </form>
</template>
