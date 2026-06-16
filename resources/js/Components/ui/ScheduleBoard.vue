<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        default: 'Calendario de Citas Programadas',
    },
    upcomingAppointments: {
        type: Array,
        required: true,
    },
    schedule: {
        type: Object,
        required: true,
    },
});

const palette = [
    'from-teal-400 to-cyan-400 text-slate-950',
    'from-cyan-500 to-blue-500 text-white',
    'from-emerald-400 to-teal-500 text-slate-950',
    'from-amber-300 to-orange-400 text-slate-950',
];

const calendarDays = computed(() => props.schedule.days.map((day, dayIndex) => {
    const appointments = props.schedule.rows
        .map((row, rowIndex) => ({
            id: `${day}-${row.time}-${rowIndex}`,
            time: row.time,
            patient: row.events[dayIndex],
            tone: palette[rowIndex % palette.length],
        }))
        .filter((appointment) => appointment.patient);

    return {
        day,
        date: props.schedule.dates?.[dayIndex] ?? String(dayIndex + 1).padStart(2, '0'),
        appointments,
    };
}));

const totalAppointments = computed(() => calendarDays.value.reduce((total, day) => total + day.appointments.length, 0));
const busiestDay = computed(() => calendarDays.value.reduce((current, day) => (day.appointments.length > current.appointments.length ? day : current), calendarDays.value[0]));
const scheduleStartIso = computed(() => props.schedule?.start_date ?? null);

const prevStartIso = computed(() => {
    if (!scheduleStartIso.value) return null;
    const date = new Date(scheduleStartIso.value + 'T00:00:00');
    date.setDate(date.getDate() - 7);
    return date.toISOString().slice(0, 10);
});

const nextStartIso = computed(() => {
    if (!scheduleStartIso.value) return null;
    const date = new Date(scheduleStartIso.value + 'T00:00:00');
    date.setDate(date.getDate() + 7);
    return date.toISOString().slice(0, 10);
});

const goToWeek = (iso) => {
    if (!iso) return;
    router.get(route('dashboard'), { start_date: iso }, { preserveState: true, replace: true });
};
</script>

<template>
    <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[#162130] shadow-2xl shadow-slate-950/20" aria-label="Calendario de citas">
        <div class="border-b border-white/10 bg-gradient-to-r from-teal-500/15 via-cyan-500/10 to-transparent p-5 sm:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">Agenda principal</p>
                    <h5 class="mt-2 text-2xl font-black text-white sm:text-3xl">{{ title }}</h5>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white disabled:opacity-40"
                        type="button"
                        :disabled="!prevStartIso"
                        aria-label="Semana anterior"
                        @click.prevent="goToWeek(prevStartIso)"
                    >
                        &lt;
                    </button>

                    <div class="rounded-2xl bg-gradient-to-r from-teal-400 to-cyan-400 px-4 py-2.5 text-sm font-black text-slate-950 shadow-lg shadow-cyan-500/20">
                        {{ schedule.rangeLabel }}
                    </div>

                    <button
                        class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white disabled:opacity-40"
                        type="button"
                        :disabled="!nextStartIso"
                        aria-label="Semana siguiente"
                        @click.prevent="goToWeek(nextStartIso)"
                    >
                        &gt;
                    </button>
                </div>
            </div>
        </div>

        <div class="grid min-w-0 gap-0 xl:grid-cols-[minmax(0,1fr)_minmax(260px,300px)]">
            <div class="min-w-0 p-4 sm:p-6">
                <div class="w-full overflow-x-auto pb-2">
                    <div class="grid min-w-[720px] grid-cols-6 gap-3 lg:min-w-0">
                        <article
                            v-for="day in calendarDays"
                            :key="day.day"
                            class="min-h-[260px] rounded-[1.5rem] border border-white/10 bg-slate-950/35 p-4 xl:min-h-[360px]"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ day.day }}</p>
                                    <p class="mt-1 text-2xl font-black text-white sm:text-3xl">{{ day.date }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-white/5 px-2.5 py-1 text-[11px] font-bold text-teal-200">
                                    {{ day.appointments.length }} citas
                                </span>
                            </div>

                            <div class="mt-5 space-y-3">
                                <div
                                    v-for="appointment in day.appointments"
                                    :key="appointment.id"
                                    class="rounded-2xl bg-gradient-to-br p-3 shadow-lg shadow-slate-950/20"
                                    :class="appointment.tone"
                                >
                                    <p class="text-xs font-black opacity-80">{{ appointment.time }}</p>
                                    <p class="mt-2 text-sm font-black leading-tight">{{ appointment.patient }}</p>
                                    <p class="mt-2 text-[11px] font-bold opacity-75">Consulta programada</p>
                                </div>

                                <div v-if="day.appointments.length === 0" class="rounded-2xl border border-dashed border-white/10 p-4 text-center">
                                    <p class="text-sm font-bold text-slate-500">Disponible</p>
                                    <p class="mt-1 text-xs text-slate-600">Sin citas asignadas</p>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <aside class="border-t border-white/10 bg-slate-950/25 p-5 xl:border-l xl:border-t-0">
                <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                    <p class="text-sm font-black text-white">Resumen semanal</p>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-teal-400/10 p-4 text-center">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">Total</p>
                            <p class="mt-2 text-xl font-black text-white">{{ totalAppointments }}</p>
                        </div>
                        <div class="rounded-2xl bg-cyan-400/10 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-cyan-300">Mayor carga</p>
                            <p class="mt-2 text-center text-xl font-black text-white">{{ busiestDay.day }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-black text-white">Proximas citas</p>
                        <span class="rounded-full bg-teal-400/10 px-3 py-1 text-xs font-bold text-teal-200">Hoy</span>
                    </div>

                    <ul class="mt-4 space-y-3">
                        <li
                            v-for="appointment in upcomingAppointments"
                            :key="appointment"
                            class="flex items-start gap-3 rounded-2xl border border-white/10 bg-slate-950/35 p-3"
                        >
                            <span class="mt-1 size-2.5 rounded-full bg-teal-300 shadow-lg shadow-teal-300/40" />
                            <span class="text-sm font-semibold leading-5 text-slate-300">{{ appointment }}</span>
                        </li>
                        <li v-if="!upcomingAppointments.length" class="rounded-2xl border border-dashed border-white/10 p-4 text-center text-sm font-semibold text-slate-500">
                            Sin citas proximas.
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>
</template>
