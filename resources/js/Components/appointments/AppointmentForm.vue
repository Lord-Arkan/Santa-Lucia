<script setup>
import { ref, watch, computed, reactive } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { appointmentService } from '@/services/appointmentService';
import DialogModal from '@/Components/DialogModal.vue';
import PatientForm from '@/Components/patients/PatientForm.vue';
import SingleSelectDropdown from '@/Components/SingleSelectDropdown.vue';
import { patientService } from '@/services/patientService';

const props = defineProps({
    form: { type: Object, required: true },
    editingAppointment: { type: Object, default: null },
    doctors: { type: Array, default: () => [] },
    patients: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    specialties: { type: Array, default: () => [] },
    isDoctor: { type: Boolean, default: false },
    myDoctor: { type: Object, default: null },
});

const emit = defineEmits(['submit', 'cancel']);

const page = usePage();
const slots = ref([]);
const loadingSlots = ref(false);

const localForm = useForm(props.form);

const minDate = computed(() => {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
});

const localPatients = ref(JSON.parse(JSON.stringify(props.patients || [])));
const showPatientModal = ref(false);
const patientForm = reactive({ ...patientService.defaultForm(), processing: false, errors: {} });

watch(() => props.form, (v) => {
    localForm.reset();
    Object.assign(localForm, v);
});

const servicesFiltered = computed(() => {
    if (!localForm.specialty_id) return props.services || [];
    return (props.services || []).filter((s) => (s.specialty_ids || []).includes(Number(localForm.specialty_id)));
});

const servicesOptions = computed(() => {
    return (servicesFiltered.value || []).map((s) => ({ ...s, label: `${s.name} (${s.duration_minutes} min)` }));
});

const doctorsFiltered = computed(() => {
    let list = props.doctors || [];
    if (localForm.specialty_id) {
        list = list.filter((d) => Number(d.specialty_id) === Number(localForm.specialty_id));
    }

    if (localForm.service_id) {
        const svc = props.services.find((x) => Number(x.service_id) === Number(localForm.service_id));
        if (svc && svc.doctor_ids && svc.doctor_ids.length) {
            list = list.filter((d) => svc.doctor_ids.includes(Number(d.doctor_id)));
        }
    }

    return list;
});

const fetchSlots = () => {
    if (!localForm.service_id || !localForm.appointment_date) {
        slots.value = [];
        return;
    }
    // Prevent fetching slots for past dates
    if (localForm.appointment_date < minDate.value) {
        slots.value = [];
        return;
    }

    loadingSlots.value = true;

    const params = { service_id: localForm.service_id, appointment_date: localForm.appointment_date };
    if (localForm.doctor_id) params.doctor_id = localForm.doctor_id;

    window.axios.get(route('appointments.slots', params))
        .then((res) => {
            slots.value = res.data.slots || [];
        })
        .finally(() => loadingSlots.value = false);
};

const useSlot = (s) => {
    localForm.start_time = s;
};

const openPatientModal = () => {
    // reset reactive patient form used inside modal
    Object.assign(patientForm, patientService.defaultForm());
    patientForm.processing = false;
    patientForm.errors = {};
    showPatientModal.value = true;
};

const submitNewPatient = () => {
    patientForm.processing = true;
    window.axios.post(route('appointments.patients.store'), patientForm)
        .then((res) => {
            if (res.data?.patient) {
                localPatients.value.push(res.data.patient);
                localForm.patient_id = res.data.patient.patient_id;
                showPatientModal.value = false;
            }
        })
        .catch((err) => {
            if (err.response && err.response.status === 422) {
                patientForm.errors = err.response.data.errors || {};
            }
        })
        .finally(() => {
            patientForm.processing = false;
        });
};

const submit = () => {
    emit('submit', localForm);
};

const cancel = () => {
    emit('cancel');
};
</script>

<template>
    <div class="p-4">
        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="block text-xs font-bold text-slate-400">Especialidad</label>
                <SingleSelectDropdown :options="props.specialties" v-model="localForm.specialty_id" value-key="specialty_id" label-key="name" placeholder="Seleccione" />
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400">Servicio</label>
                <SingleSelectDropdown :options="servicesOptions" v-model="localForm.service_id" value-key="service_id" label-key="label" placeholder="Seleccione" />
                <span v-if="localForm.errors.service_id" class="mt-1 block text-xs font-bold text-rose-300">{{ localForm.errors.service_id }}</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400">Doctor</label>
                <SingleSelectDropdown :options="doctorsFiltered" v-model="localForm.doctor_id" value-key="doctor_id" label-key="name" placeholder="Seleccione" />
                <span v-if="localForm.errors.doctor_id" class="mt-1 block text-xs font-bold text-rose-300">{{ localForm.errors.doctor_id }}</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400">Fecha</label>
                <input v-model="localForm.appointment_date" :min="minDate" type="date" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none" />
                <span v-if="localForm.errors.appointment_date" class="mt-1 block text-xs font-bold text-rose-300">{{ localForm.errors.appointment_date }}</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400">Hora (seleccione un slot)</label>
                <input v-model="localForm.start_time" type="text" placeholder="HH:MM" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none" readonly />
                <span v-if="localForm.errors.start_time" class="mt-1 block text-xs font-bold text-rose-300">{{ localForm.errors.start_time }}</span>
            </div>

            <div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-400">Paciente</label>
                    <button type="button" class="text-sm font-bold text-teal-300 hover:underline" @click="openPatientModal">+ Nuevo</button>
                </div>

                <SingleSelectDropdown :options="localPatients" v-model="localForm.patient_id" value-key="patient_id" label-key="name" placeholder="Seleccione" />
                <span v-if="localForm.errors.patient_id" class="mt-1 block text-xs font-bold text-rose-300">{{ localForm.errors.patient_id }}</span>
            </div>
        </div>

        <div class="mt-4">
            <button type="button" class="rounded-2xl bg-white/5 px-3 py-2 text-sm font-bold text-slate-300 hover:bg-white/10" @click="fetchSlots" :disabled="loadingSlots">Buscar disponibilidad</button>
        </div>

        <div class="mt-4">
            <div v-if="loadingSlots" class="text-sm text-slate-400">Cargando slots...</div>
            <div v-else class="flex flex-wrap gap-2">
                <button v-for="s in slots" :key="s" type="button" class="rounded-full border px-3 py-1 text-xs font-black text-slate-300 hover:bg-white/10" @click="useSlot(s)">{{ s }}</button>
                <div v-if="!loadingSlots && slots.length === 0" class="text-sm text-slate-500">Sin slots disponibles</div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-black text-slate-300" @click="cancel">Cancelar</button>
            <button type="button" class="rounded-2xl bg-gradient-to-r from-teal-400 to-cyan-400 px-4 py-2 text-sm font-black text-slate-950" @click="submit">Reservar</button>
        </div>
        
        <DialogModal :show="showPatientModal" @close="showPatientModal = false" max-width="2xl">
            <template #content>
                <PatientForm :form="patientForm" @submit="submitNewPatient" @cancel="showPatientModal = false" />
            </template>
        </DialogModal>
    </div>
</template>
