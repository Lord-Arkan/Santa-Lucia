<script setup>
import { computed } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    editingSpecialty: { type: Object, default: null },
});

const emit = defineEmits(['submit', 'cancel']);
const isEditing = computed(() => Boolean(props.editingSpecialty));
</script>

<template>
    <form class="relative rounded-[2rem] border border-white/10 bg-[#162130] p-5 shadow-xl shadow-slate-950/10" @submit.prevent="$emit('submit')">
        <button
            type="button"
            @click="$emit('cancel')"
            aria-label="Cerrar"
            class="absolute right-3 top-3 z-20 flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-[#0b1620] text-slate-300 shadow-md transition hover:bg-white/10 hover:text-white"
        >
            <span class="sr-only">Cerrar</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">{{ isEditing ? 'Editar especialidad' : 'Nueva especialidad' }}</p>

        <div class="mt-6 grid gap-4">
            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Nombre</span>
                <input v-model="form.name" type="text" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="Nombre de especialidad">
                <span v-if="form.errors.name" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.name }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Estado</span>
                <select v-model="form.status" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-sm font-semibold text-white outline-none transition">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
                <span v-if="form.errors.status" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.status }}</span>
            </label>
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="mt-6 h-12 w-full rounded-2xl bg-gradient-to-r from-teal-400 to-cyan-400 px-5 text-sm font-black text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-70"
        >
            {{ form.processing ? 'Guardando...' : (isEditing ? 'Actualizar especialidad' : 'Crear especialidad') }}
        </button>
    </form>
</template>
