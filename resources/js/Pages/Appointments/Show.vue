<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
  appointment: { type: Object, required: true },
});

const statusClass = (status) => {
  if (!status) return 'bg-white/5 text-slate-300';
  switch (status.toString().toUpperCase()) {
    case 'SCHEDULED':
      return 'bg-emerald-300/10 text-emerald-200';
    case 'COMPLETED':
      return 'bg-sky-300/10 text-sky-200';
    case 'CANCELLED':
      return 'bg-rose-300/10 text-rose-200';
    default:
      return 'bg-white/5 text-slate-300';
  }
};

const patient = computed(() => props.appointment.patient ?? {});
const doctor = computed(() => props.appointment.doctor ?? {});
const service = computed(() => props.appointment.service ?? {});

const goBack = () => router.get(route('appointments.index'));
</script>

<template>
  <Head title="Detalle de cita" />

  <DashboardLayout>
  <div class="grid gap-6 max-w-6xl mx-auto px-4">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-black text-white">Detalle de cita</h2>
          <p class="text-sm text-slate-400">Vista ordenada estilo factura para una lectura rápida.</p>
        </div>

        <div class="flex items-center gap-2">
          <button type="button" @click="goBack" class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-bold text-slate-300 hover:bg-white/10">Volver</button>
        </div>
      </div>

      <section class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-slate-950/25 p-6">
        <div class="flex flex-col gap-6">

          <!-- Servicio (arriba) -->
          <div class="w-full">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Servicio</p>
                <h3 class="mt-2 text-xl lg:text-2xl font-black text-white">{{ service.name ?? '-' }}</h3>
                <p class="mt-1 text-sm text-slate-400">Doctor: {{ doctor.name ?? '-' }} · {{ doctor.specialty ?? '-' }}</p>
              </div>

              <div class="flex flex-col items-end text-sm text-slate-400">
                <div class="flex items-center gap-3">
                  <span :class="['rounded-full px-3 py-1.5 text-xs font-black', statusClass(props.appointment.status)]">{{ props.appointment.status_label }}</span>
                </div>
                <div class="mt-2">Creada: {{ props.appointment.created_at }}</div>
              </div>
            </div>

            <div class="mt-6 space-y-2 text-sm text-slate-400">
              <div><span class="font-bold text-slate-300">Descripción:</span> {{ service.description ?? service.name ?? '-' }}</div>
              <div><span class="font-bold text-slate-300">Duración:</span> {{ service.duration_minutes ?? '-' }} min</div>
              <div><span class="font-bold text-slate-300">Fecha:</span> {{ props.appointment.appointment_date }}</div>
              <div><span class="font-bold text-slate-300">Horario:</span> {{ props.appointment.start_time }} - {{ props.appointment.end_time }}</div>
            </div>
          </div>

          <hr class="border-t border-white/6" />

          <!-- Paciente (medio) -->
          <div class="w-full">
            <p class="text-xs font-bold text-slate-400 uppercase">Paciente</p>
            <h3 class="mt-2 text-xl lg:text-2xl font-black text-white">{{ patient.name ?? 'Paciente' }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ patient.document_type ? (patient.document_type + ' - ') : '' }}{{ patient.document_number ?? '' }}</p>

            <div class="mt-6 space-y-2 text-sm text-slate-400">
              <div><span class="font-bold text-slate-300">Teléfono:</span> {{ patient.phone ?? '-' }}</div>
              <div><span class="font-bold text-slate-300">Email:</span> {{ patient.email ?? '-' }}</div>
              <div><span class="font-bold text-slate-300">Nacimiento:</span> {{ patient.birth_date ?? '-' }}</div>
              <div><span class="font-bold text-slate-300">Alergias:</span> {{ patient.allergies ?? '-' }}</div>
            </div>
          </div>

          <hr class="border-t border-white/6" />

          <!-- Doctor (abajo) -->
          <aside class="w-full">
            <p class="text-xs font-bold text-slate-400 uppercase">Doctor</p>
            <h3 class="mt-2 text-xl lg:text-2xl font-black text-white">{{ doctor.name ?? '-' }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ doctor.degree ?? '' }}</p>

            <div class="mt-6 space-y-2 text-sm text-slate-400">
              <div><span class="font-bold text-slate-300">Especialidad:</span> {{ doctor.specialty ?? '-' }}</div>
              <div><span class="font-bold text-slate-300">Licencia:</span> {{ doctor.license_number ?? '-' }}</div>
              <div><span class="font-bold text-slate-300">Estado:</span> {{ doctor.status ?? '-' }}</div>
            </div>
          </aside>
        </div>
      </section>
    </div>
  </DashboardLayout>
</template>
