<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    modules: {
        type: Array,
        required: true,
    },
    editingUser: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['submit', 'cancel']);
const imagePreview = ref('');
const isEditing = computed(() => Boolean(props.editingUser));

const onImageChange = (event) => {
    const [file] = event.target.files;
    props.form.image = file ?? null;
    imagePreview.value = file ? URL.createObjectURL(file) : '';
};

watch(() => props.editingUser, () => {
    imagePreview.value = '';
});
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
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">{{ isEditing ? 'Editar usuario' : 'Nuevo usuario' }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4">
            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Nombre</span>
                <input
                    v-model="form.name"
                    type="text"
                    class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition placeholder:text-slate-500 focus:border-teal-300/60 focus:ring-4 focus:ring-teal-400/10"
                    placeholder="Nombre completo"
                >
                <span v-if="form.errors.name" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.name }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Email</span>
                <input
                    v-model="form.email"
                    type="email"
                    class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition placeholder:text-slate-500 focus:border-teal-300/60 focus:ring-4 focus:ring-teal-400/10"
                    placeholder="usuario@santalucia.test"
                >
                <span v-if="form.errors.email" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.email }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Rol</span>
                <select
                    v-model="form.rol"
                    class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-sm font-semibold text-white outline-none transition focus:border-teal-300/60 focus:ring-4 focus:ring-teal-400/10"
                >
                    <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                </select>
                <span v-if="form.errors.rol" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.rol }}</span>
            </label>

            <fieldset>
                <legend class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Acceso por modulos</legend>
                <p class="mt-2 text-xs font-medium text-slate-500">Los modulos no seleccionados se ocultaran y no seran accesibles.</p>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <label
                        v-for="module in modules"
                        :key="module.value"
                        class="flex cursor-pointer gap-3 rounded-2xl border border-white/10 bg-white/5 p-3 transition hover:border-teal-300/40 hover:bg-white/10"
                    >
                        <input
                            v-model="form.module_permissions"
                            type="checkbox"
                            :value="module.value"
                            class="mt-1 size-4 rounded border-white/20 bg-[#101824] text-teal-400 focus:ring-teal-400/30"
                        >
                        <span>
                            <span class="block text-sm font-bold text-white">{{ module.label }}</span>
                            <span class="mt-1 block text-xs font-medium text-slate-500">{{ module.description }}</span>
                        </span>
                    </label>
                </div>
                <span v-if="form.errors.module_permissions" class="mt-2 block text-xs font-bold text-rose-300">{{ form.errors.module_permissions }}</span>
            </fieldset>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Password</span>
                    <input
                        v-model="form.password"
                        type="password"
                        class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition placeholder:text-slate-500 focus:border-teal-300/60 focus:ring-4 focus:ring-teal-400/10"
                        :placeholder="isEditing ? 'Dejar vacio para mantener' : 'Minimo 8 caracteres'"
                    >
                    <span v-if="form.errors.password" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.password }}</span>
                </label>

                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Confirmar</span>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition placeholder:text-slate-500 focus:border-teal-300/60 focus:ring-4 focus:ring-teal-400/10"
                        placeholder="Repetir password"
                    >
                </label>
            </div>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Imagen de perfil</span>
                <input
                    type="file"
                    accept="image/png,image/jpeg"
                    class="mt-2 block w-full rounded-2xl border border-dashed border-white/10 bg-white/5 px-4 py-4 text-sm text-slate-300 file:mr-4 file:rounded-xl file:border-0 file:bg-teal-400 file:px-4 file:py-2 file:text-sm file:font-black file:text-slate-950 hover:bg-white/10"
                    @change="onImageChange"
                >
                <span v-if="form.errors.image" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.image }}</span>
            </label>

            <div v-if="imagePreview || editingUser?.profile_photo_path" class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3">
                <img :src="imagePreview || editingUser.profile_photo_url" alt="Vista previa" class="size-12 rounded-full object-cover">
                <span class="text-sm font-semibold text-slate-300">Vista previa de imagen</span>
            </div>
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="mt-6 h-12 w-full rounded-2xl bg-gradient-to-r from-teal-400 to-cyan-400 px-5 text-sm font-black text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-70"
        >
            {{ form.processing ? 'Guardando...' : (isEditing ? 'Actualizar usuario' : 'Crear usuario') }}
        </button>
    </form>
</template>
