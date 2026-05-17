<script setup>
import { computed } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    editingDoctor: { type: Object, default: null },
    users: { type: Array, default: () => [] },
    specialties: { type: Array, default: () => [] },
});

const emit = defineEmits(['submit', 'cancel']);
const isEditing = computed(() => Boolean(props.editingDoctor));
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
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">{{ isEditing ? 'Editar doctor' : 'Nuevo doctor' }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Usuario (rol doctor)</span>
                <select v-model="form.user_id" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-sm font-semibold text-white outline-none transition">
                    <option :value="null" disabled>Seleccionar usuario</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
                <span v-if="form.errors.user_id" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.user_id }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Especialidad</span>
                <select v-model="form.specialty_id" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-sm font-semibold text-white outline-none transition">
                    <option :value="null" disabled>Seleccionar especialidad</option>
                    <option v-for="s in specialties" :key="s.specialty_id" :value="s.specialty_id">{{ s.name }}</option>
                </select>
                <span v-if="form.errors.specialty_id" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.specialty_id }}</span>
            </label>

            <label class="block sm:col-span-2">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Número de licencia</span>
                <input v-model="form.license_number" type="text" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="Número de licencia">
                <span v-if="form.errors.license_number" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.license_number }}</span>
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
            {{ form.processing ? 'Guardando...' : (isEditing ? 'Actualizar doctor' : 'Crear doctor') }}
        </button>
    </form>
</template>
