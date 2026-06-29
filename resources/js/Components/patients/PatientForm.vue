<script setup>
import { computed, ref, watch, onMounted, nextTick } from 'vue';
import { onUnmounted } from 'vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    editingPatient: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['submit', 'cancel']);
const isEditing = computed(() => Boolean(props.editingPatient));

const dateInput = ref(null);
let fpInstance = null;

    onMounted(async () => {
    await nextTick();

    try {
        const fpModule = await import('flatpickr');
        const flatpickr = fpModule.default ?? fpModule;

        // Inject a modern theme (airbnb) from CDN at runtime
        if (!document.querySelector('link[data-flatpickr]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css';
            link.setAttribute('data-flatpickr', 'true');
            document.head.appendChild(link);
        }

        // try to localize to Spanish if available
        try {
            const localeModule = await import('flatpickr/dist/l10n/es.js');
            const esLocale = localeModule.default?.es ?? localeModule.es ?? localeModule.default ?? localeModule;
            if (esLocale && typeof flatpickr.localize === 'function') {
                flatpickr.localize(esLocale);
            }
        } catch (e) {
            // ignore if locale not available
        }

        fpInstance = flatpickr(dateInput.value, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true,
            defaultDate: props.form.birth_date || null,
            monthSelectorType: 'dropdown',
            // show a single month but with a compact modern look
            showMonths: 1,
            onChange: (selectedDates, dateStr) => {
                props.form.birth_date = dateStr || null;
            },
        });
    } catch (e) {
        // flatpickr not available -> fallback to native date input
        if (dateInput.value) {
            dateInput.value.type = 'date';
        }
    }
});

onUnmounted(() => {
    if (fpInstance && typeof fpInstance.destroy === 'function') {
        try { fpInstance.destroy(); } catch (e) { /* ignore */ }
    }
});

watch(() => props.form.birth_date, (val) => {
    if (fpInstance) {
        try {
            fpInstance.setDate(val || null, false);
        } catch (e) {
            // ignore
        }
    }
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
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">{{ isEditing ? 'Editar paciente' : 'Nuevo paciente' }}</p>
                
                
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Tipo documento</span>
                <select v-model="form.document_type" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-sm font-semibold text-white outline-none transition">
                    <option value="DNI">DNI</option>
                    <option value="PAS">Pasaporte</option>
                    <option value="OTR">Otro</option>
                </select>
                <span v-if="form.errors.document_type" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.document_type }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Nº documento</span>
                <input v-model="form.document_number" type="text" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition placeholder:text-slate-500" placeholder="Número de documento">
                <span v-if="form.errors.document_number" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.document_number }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Nombres</span>
                <input v-model="form.first_name" type="text" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="Nombres">
                <span v-if="form.errors.first_name" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.first_name }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Apellidos</span>
                <input v-model="form.last_name" type="text" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="Apellidos">
                <span v-if="form.errors.last_name" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.last_name }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Fecha de nacimiento</span>
                <input ref="dateInput" v-model="form.birth_date" type="text" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="AAAA-MM-DD">
                <span class="mt-1 block text-xs text-slate-400">Formato: <strong>DD/MM/YYYY</strong> (visual) — el campo guarda ISO <strong>AAAA-MM-DD</strong></span>
                <span v-if="form.errors.birth_date" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.birth_date }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Género</span>
                <select v-model="form.gender" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-sm font-semibold text-white outline-none transition">
                    <option value="">Seleccionar</option>
                    <option value="male">Masculino</option>
                    <option value="female">Femenino</option>
                    <option value="other">Otro</option>
                </select>
                <span v-if="form.errors.gender" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.gender }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Teléfono</span>
                <input v-model="form.phone" type="text" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="Teléfono">
                <span v-if="form.errors.phone" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.phone }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Email</span>
                <input v-model="form.email" type="email" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="Correo electrónico">
                <span v-if="form.errors.email" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.email }}</span>
            </label>

            <label class="block sm:col-span-2">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Dirección</span>
                <textarea v-model="form.address" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white outline-none transition" placeholder="Dirección"></textarea>
                <span v-if="form.errors.address" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.address }}</span>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Tipo sangre</span>
                <select v-model="form.blood_type" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-[#101824] px-4 text-sm font-semibold text-white outline-none transition">
                    <option value="">--</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
                <span v-if="form.errors.blood_type" class="mt-1 block text-xs font-bold text-rose-300">{{ form.errors.blood_type }}</span>
            </label>

            <label class="block sm:col-span-2">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Alergias</span>
                <textarea v-model="form.allergies" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white outline-none transition" placeholder="Alergias"></textarea>
            </label>

            <label class="block sm:col-span-2">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Condiciones previas</span>
                <textarea v-model="form.previous_conditions" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white outline-none transition" placeholder="Condiciones previas"></textarea>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Seguro</span>
                <input v-model="form.insurance_type" type="text" class="mt-2 h-12 w-full rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white outline-none transition" placeholder="Seguro medico">
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
            {{ form.processing ? 'Guardando...' : (isEditing ? 'Actualizar paciente' : 'Crear paciente') }}
        </button>
    </form>
</template>
