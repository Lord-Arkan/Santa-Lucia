<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { toRelativeUrl } from '@/utils/navigation';

const props = defineProps({
    filters: { type: Object, required: true },
    summary: { type: Object, required: true },
    charts: { type: Object, required: true },
    periodReport: { type: Object, required: true },
    doctorReport: { type: Object, required: true },
    specialtyReport: { type: Object, required: true },
    patientHistory: { type: Object, required: true },
    absenceReport: { type: Object, required: true },
    newPatientsReport: { type: Object, required: true },
    doctors: { type: Array, default: () => [] },
    specialties: { type: Array, default: () => [] },
});

const tabs = [
    { key: 'dashboard', label: 'General', description: 'Resumen ejecutivo de citas y atencion.' },
    { key: 'period', label: 'Por periodo', description: 'Evolucion de citas por dia, mes o anio.' },
    { key: 'doctor', label: 'Por medico', description: 'Carga y resultados por profesional.' },
    { key: 'specialty', label: 'Especialidades', description: 'Demanda por especialidad medica.' },
    { key: 'patient', label: 'Historial paciente', description: 'Detalle de citas por paciente.' },
    { key: 'absences', label: 'Inasistencias', description: 'Cancelaciones y pacientes que no asistieron.' },
    { key: 'new_patients', label: 'Pacientes nuevos', description: 'Altas de pacientes en el periodo.' },
];

const activeTab = ref('dashboard');
const showFilters = ref(false);
const localSearch = ref('');
const filters = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    doctor_id: props.filters.doctor_id,
    specialty_id: props.filters.specialty_id,
    patient_search: props.filters.patient_search,
});

const kpis = computed(() => [
    { label: 'Total de citas', value: props.summary.total_appointments, detail: 'Registradas en el periodo', tone: 'teal' },
    { label: 'Citas de hoy', value: props.summary.today_appointments, detail: 'Dentro del rango actual', tone: 'sky' },
    { label: 'Pendientes', value: props.summary.pending_appointments, detail: 'Programadas sin atencion', tone: 'amber' },
    { label: 'Atendidas', value: props.summary.attended_appointments, detail: 'Cerradas correctamente', tone: 'emerald' },
    { label: 'Canceladas', value: props.summary.cancelled_appointments, detail: 'Canceladas por el flujo', tone: 'rose' },
    { label: 'Pacientes atendidos', value: props.summary.attended_patients, detail: 'Pacientes unicos atendidos', tone: 'violet' },
    { label: 'Medico destacado', value: props.summary.top_doctor, detail: `${props.summary.top_doctor_total} citas`, tone: 'cyan' },
    { label: 'Especialidad destacada', value: props.summary.top_specialty, detail: `${props.summary.top_specialty_total} citas`, tone: 'teal' },
]);

const paginatorMap = computed(() => ({
    period: props.periodReport.rows,
    doctor: props.doctorReport.rows,
    specialty: props.specialtyReport.rows,
    patient: props.patientHistory.rows,
    absences: props.absenceReport.rows,
    new_patients: props.newPatientsReport.rows,
}));

const currentPaginator = computed(() => paginatorMap.value[activeTab.value] ?? null);
const rows = computed(() => {
    const term = localSearch.value.trim().toLowerCase();
    const source = currentPaginator.value?.data ?? [];

    if (!term) {
        return source;
    }

    return source.filter((row) => Object.values(row).some((value) => String(value ?? '').toLowerCase().includes(term)));
});

const maxPeriod = computed(() => Math.max(...(props.charts.period ?? []).map((item) => item.total), 1));
const maxStatus = computed(() => Math.max(...(props.charts.status ?? []).map((item) => item.value), 1));
const maxSpecialty = computed(() => Math.max(...(props.charts.specialties ?? []).map((item) => item.total), 1));
const maxRows = computed(() => Math.max(...rows.value.map((row) => Number(row.total ?? 1)), 1));
const statusTotal = computed(() => (props.charts.status ?? []).reduce((sum, item) => sum + Number(item.value ?? 0), 0));
const chartPalette = ['#5eead4', '#7dd3fc', '#a7f3d0', '#fcd34d', '#fda4af', '#c4b5fd'];

const activeTabLabel = computed(() => tabs.find((tab) => tab.key === activeTab.value)?.label ?? '');
const activeTabDescription = computed(() => tabs.find((tab) => tab.key === activeTab.value)?.description ?? '');
const groupLabel = computed(() => ({
    day: 'Dias',
    month: 'Meses',
    year: 'Anios',
})[props.filters.group_by] ?? 'Dias');

const dateLabel = (date) => {
    if (!date) return '';
    const [year, month, day] = String(date).split('-');

    return [day, month, year].filter(Boolean).join('/');
};

const rangeLabel = computed(() => `${dateLabel(filters.value.start_date)} - ${dateLabel(filters.value.end_date)}`);

const exportOptions = [
    { format: 'csv', label: 'CSV', detail: 'Datos planos' },
    { format: 'xlsx', label: 'Excel', detail: 'Tabla formateada' },
    { format: 'pdf', label: 'PDF', detail: 'Vista imprimible' },
];

const kpiToneClass = (tone) => ({
    teal: 'border-teal-300/20 bg-teal-400/10 text-teal-200',
    sky: 'border-sky-300/20 bg-sky-400/10 text-sky-200',
    amber: 'border-amber-300/20 bg-amber-400/10 text-amber-200',
    emerald: 'border-emerald-300/20 bg-emerald-400/10 text-emerald-200',
    rose: 'border-rose-300/20 bg-rose-400/10 text-rose-200',
    violet: 'border-violet-300/20 bg-violet-400/10 text-violet-200',
    cyan: 'border-cyan-300/20 bg-cyan-400/10 text-cyan-200',
}[tone] ?? 'border-white/10 bg-white/5 text-slate-200');

const statusSegments = computed(() => {
    let offset = 25;

    return (props.charts.status ?? []).map((item, index) => {
        const value = Number(item.value ?? 0);
        const dash = statusTotal.value > 0 ? (value / statusTotal.value) * 100 : 0;
        const segment = {
            ...item,
            color: chartPalette[index % chartPalette.length],
            dash,
            offset,
        };
        offset -= dash;

        return segment;
    });
});

const areaPoints = computed(() => {
    const period = props.charts.period ?? [];
    const width = 320;
    const height = 150;

    if (!period.length) {
        return [];
    }

    return period.map((item, index) => {
        const x = period.length === 1 ? width / 2 : (index / (period.length - 1)) * width;
        const y = height - ((Number(item.total ?? 0) / maxPeriod.value) * (height - 12)) - 6;

        return { x, y, label: item.period, total: item.total };
    });
});

const areaLine = computed(() => areaPoints.value.map((point) => `${point.x},${point.y}`).join(' '));
const areaFill = computed(() => {
    if (!areaPoints.value.length) return '';
    const last = areaPoints.value[areaPoints.value.length - 1];

    return `0,150 ${areaLine.value} ${last.x},150`;
});

const cleanFilters = () => Object.fromEntries(Object.entries(filters.value).filter(([, value]) => value !== '' && value !== null && value !== undefined));

const applyFilters = () => {
    router.get(route('reports.index'), cleanFilters(), { preserveState: true, replace: true });
};

const clearFilters = () => {
    filters.value = {
        start_date: '',
        end_date: '',
        doctor_id: '',
        specialty_id: '',
        patient_search: '',
    };
    router.get(route('reports.index'), {}, { preserveState: true, replace: true });
};

const exportReport = (format) => {
    const params = new URLSearchParams({ ...cleanFilters(), section: activeTab.value });
    window.open(`${route('reports.export', { format })}?${params.toString()}`, '_blank');
};

const printReport = () => {
    window.print();
};

const goTo = (url) => {
    if (!url) return;
    router.get(toRelativeUrl(url), {}, { preserveState: true, replace: true });
};

const translateLabel = (label) => String(label || '')
    .replace(/pagination\.previous/g, 'Anterior')
    .replace(/pagination\.next/g, 'Siguiente')
    .replace(/Previous|previous/g, 'Anterior')
    .replace(/Next|next/g, 'Siguiente');

const setTab = (tab) => {
    activeTab.value = tab;
    localSearch.value = '';
};

const rowLabel = (row) => row.period ?? row.doctor ?? row.specialty ?? row.patient ?? 'Registro';
const rowValue = (row) => Number(row.total ?? 1);
const rowPercent = (row) => `${Math.max((rowValue(row) / maxRows.value) * 100, 4)}%`;
const trendRows = computed(() => (activeTab.value === 'new_patients' ? props.newPatientsReport.rows.data ?? [] : rows.value));
const trendMax = computed(() => Math.max(...trendRows.value.map((row) => Number(row.total ?? 0)), 1));
const trendHeight = (row) => `${Math.max((Number(row.total ?? 0) / trendMax.value) * 180, 8)}px`;

const statusClass = (status) => {
    if (status === 'Atendida') return 'bg-emerald-300/10 text-emerald-200';
    if (status === 'Cancelada') return 'bg-rose-300/10 text-rose-200';
    if (status === 'No asistio') return 'bg-amber-300/10 text-amber-200';
    return 'bg-sky-300/10 text-sky-200';
};
</script>

<template>
    <Head title="Reportes" />

    <DashboardLayout>
        <div class="grid min-w-0 max-w-full gap-4 overflow-hidden sm:gap-6">
            <section class="rounded-2xl border border-white/10 bg-[#162130] p-4 shadow-xl shadow-slate-950/10 sm:rounded-[2rem] sm:p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-teal-300">Centro de reportes</p>
                        <h2 class="mt-2 text-xl font-black text-white sm:text-2xl">Reportes clinicos y operativos</h2>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-400">
                            {{ activeTabDescription }} Periodo: {{ rangeLabel }}.
                        </p>
                    </div>

                    <button type="button" class="w-full rounded-xl bg-white/5 px-3 py-2 text-xs font-bold text-slate-300 hover:bg-white/10 sm:w-auto sm:rounded-2xl sm:text-sm" @click="showFilters = !showFilters">
                        {{ showFilters ? 'Ocultar filtros' : 'Filtros' }}
                    </button>
                </div>

                <div class="mt-5 grid gap-2 sm:grid-cols-3 xl:grid-cols-[repeat(3,minmax(160px,1fr))_auto]">
                    <button
                        v-for="option in exportOptions"
                        :key="option.format"
                        type="button"
                        class="flex items-center justify-between rounded-xl border border-white/10 bg-slate-950/25 px-3 py-3 text-left transition hover:border-teal-300/30 hover:bg-teal-400/10"
                        @click="exportReport(option.format)"
                    >
                        <span>
                            <span class="block text-xs font-black uppercase tracking-[0.14em] text-white">{{ option.label }}</span>
                            <span class="mt-1 block text-[11px] font-semibold text-slate-500">{{ option.detail }}</span>
                        </span>
                        <svg viewBox="0 0 24 24" class="size-4 text-teal-200" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" aria-hidden="true">
                            <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                        </svg>
                    </button>

                    <button type="button" class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-xs font-black text-slate-300 transition hover:bg-white/10 xl:min-w-32" @click="printReport">
                        Imprimir vista
                    </button>
                </div>
            </section>

            <transition name="fade">
                <section v-show="showFilters" class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-4 shadow-xl shadow-slate-950/10 sm:rounded-[2rem] sm:p-5">
                    <form class="grid gap-3 lg:grid-cols-6" @submit.prevent="applyFilters">
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Inicio</span>
                            <input v-model="filters.start_date" type="date" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none">
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Fin</span>
                            <input v-model="filters.end_date" type="date" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none">
                        </label>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2">
                            <span class="block text-xs font-bold text-slate-400">Agrupacion</span>
                            <span class="mt-1 block text-sm font-black text-teal-200">{{ groupLabel }}</span>
                        </div>
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Medico</span>
                            <select v-model="filters.doctor_id" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                                <option value="">Todos</option>
                                <option v-for="doctor in props.doctors" :key="doctor.doctor_id" :value="doctor.doctor_id">{{ doctor.name }}</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Especialidad</span>
                            <select v-model="filters.specialty_id" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                                <option value="">Todas</option>
                                <option v-for="specialty in props.specialties" :key="specialty.specialty_id" :value="specialty.specialty_id">{{ specialty.name }}</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Paciente</span>
                            <input v-model="filters.patient_search" type="search" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Nombre, DNI o codigo">
                        </label>

                        <div class="flex flex-wrap gap-2 lg:col-span-6">
                            <button type="submit" class="h-10 rounded-2xl bg-teal-400/80 px-4 text-sm font-black text-slate-950">Aplicar filtros</button>
                            <button type="button" class="h-10 rounded-2xl border border-white/10 px-4 text-sm font-black text-slate-300" @click="clearFilters">Limpiar</button>
                        </div>
                    </form>
                </section>
            </transition>

            <div class="min-w-0 max-w-full overflow-x-auto pb-1">
                <div class="flex w-max max-w-none gap-2">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="shrink-0 rounded-xl border px-3 py-2 text-[11px] font-black transition sm:rounded-2xl sm:px-4 sm:text-xs"
                    :class="activeTab === tab.key ? 'border-teal-300/30 bg-teal-400/15 text-white' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'"
                    @click="setTab(tab.key)"
                >
                    {{ tab.label }}
                </button>
                </div>
            </div>

            <template v-if="activeTab === 'dashboard'">
                <section class="grid min-w-0 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article v-for="item in kpis" :key="item.label" class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">{{ item.label }}</p>
                            <span :class="['size-2.5 shrink-0 rounded-full border', kpiToneClass(item.tone)]"></span>
                        </div>
                        <p class="mt-3 break-words text-2xl font-black text-white">{{ item.value }}</p>
                        <p class="mt-2 text-xs font-semibold text-slate-500">{{ item.detail }}</p>
                    </article>
                </section>

                <section class="grid min-w-0 gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    <article class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-4 sm:rounded-[2rem] sm:p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Estados</p>
                        <div class="mt-5 grid gap-5 sm:grid-cols-[180px_1fr] xl:grid-cols-1 2xl:grid-cols-[180px_1fr]">
                            <div class="relative mx-auto size-44">
                                <svg viewBox="0 0 42 42" class="size-full -rotate-90">
                                    <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="rgba(15,23,42,.8)" stroke-width="5" />
                                    <circle
                                        v-for="segment in statusSegments"
                                        :key="segment.label"
                                        cx="21"
                                        cy="21"
                                        r="15.915"
                                        fill="transparent"
                                        :stroke="segment.color"
                                        stroke-width="5"
                                        :stroke-dasharray="`${segment.dash} ${100 - segment.dash}`"
                                        :stroke-dashoffset="segment.offset"
                                    />
                                </svg>
                                <div class="absolute inset-0 grid place-items-center text-center">
                                    <span>
                                        <span class="block text-3xl font-black text-white">{{ statusTotal }}</span>
                                        <span class="text-xs font-bold text-slate-500">citas</span>
                                    </span>
                                </div>
                            </div>
                            <div class="grid content-center gap-3">
                                <div v-for="segment in statusSegments" :key="segment.label" class="flex items-center justify-between gap-3 text-xs font-bold text-slate-400">
                                    <span class="flex min-w-0 items-center gap-2">
                                        <span class="size-2.5 shrink-0 rounded-full" :style="{ backgroundColor: segment.color }"></span>
                                        <span class="truncate">{{ segment.label }}</span>
                                    </span>
                                    <span>{{ segment.value }}</span>
                                </div>
                                <p v-if="!statusSegments.length" class="py-8 text-center text-sm font-semibold text-slate-500">Sin registros.</p>
                            </div>
                        </div>
                    </article>

                    <article class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-4 sm:rounded-[2rem] sm:p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Especialidades</p>
                        <div class="mt-5 grid gap-3">
                            <div v-for="(item, index) in props.charts.specialties" :key="item.specialty" class="rounded-2xl border border-white/10 bg-slate-950/30 p-3">
                                <div class="mb-2 flex justify-between gap-3 text-xs font-bold text-slate-400">
                                    <span class="truncate">{{ item.specialty }}</span>
                                    <span>{{ item.total }}</span>
                                </div>
                                <div class="h-3 overflow-hidden rounded-full bg-slate-950/60">
                                    <div class="h-full rounded-full" :style="{ width: `${(item.total / maxSpecialty) * 100}%`, backgroundColor: chartPalette[index % chartPalette.length] }"></div>
                                </div>
                            </div>
                            <p v-if="!props.charts.specialties.length" class="py-8 text-center text-sm font-semibold text-slate-500">Sin registros.</p>
                        </div>
                    </article>
                </section>

                <section class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-4 sm:rounded-[2rem] sm:p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Tendencia por periodo</p>
                    <div class="mt-5 rounded-2xl border border-white/10 bg-slate-950/30 p-4">
                        <svg viewBox="0 0 320 170" class="h-56 w-full sm:h-64" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="reportArea" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#67e8f9" stop-opacity="0.7" />
                                    <stop offset="100%" stop-color="#14b8a6" stop-opacity="0.05" />
                                </linearGradient>
                            </defs>
                            <polyline v-if="areaPoints.length" :points="areaFill" fill="url(#reportArea)" stroke="none" />
                            <polyline v-if="areaPoints.length" :points="areaLine" fill="none" stroke="#67e8f9" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                            <circle v-for="point in areaPoints" :key="point.label" :cx="point.x" :cy="point.y" r="2.4" fill="#5eead4" />
                        </svg>
                        <div class="flex justify-between gap-2 text-[10px] font-bold text-slate-500">
                            <span>{{ props.charts.period?.[0]?.period ?? '' }}</span>
                            <span>{{ props.charts.period?.[props.charts.period.length - 1]?.period ?? '' }}</span>
                        </div>
                        <p v-if="!areaPoints.length" class="py-8 text-center text-sm font-semibold text-slate-500">Sin registros.</p>
                    </div>
                </section>

                <section class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-4 sm:rounded-[2rem] sm:p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Barras por periodo</p>
                    <div class="mt-5 flex h-64 max-w-full items-end gap-2 overflow-x-auto">
                            <div v-for="item in props.charts.period" :key="item.period" class="flex min-w-14 flex-1 flex-col items-center gap-2">
                            <div class="w-full rounded-t-xl bg-gradient-to-t from-teal-400 to-cyan-300 shadow-lg shadow-cyan-500/10" :style="{ height: `${Math.max((item.total / maxPeriod) * 220, 8)}px` }"></div>
                            <span class="max-w-20 truncate text-[10px] font-bold text-slate-500">{{ item.period }}</span>
                        </div>
                    </div>
                </section>
            </template>

            <template v-else>
                <section v-if="activeTab === 'absences'" class="grid min-w-0 gap-4 sm:grid-cols-3">
                    <article class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Citas evaluadas</p>
                        <p class="mt-3 text-2xl font-black text-white">{{ props.absenceReport.stats.total }}</p>
                    </article>
                    <article class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Canceladas / no asistio</p>
                        <p class="mt-3 text-2xl font-black text-white">{{ props.absenceReport.stats.absences }}</p>
                    </article>
                    <article class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Ausentismo</p>
                        <p class="mt-3 text-2xl font-black text-white">{{ props.absenceReport.stats.rate }}%</p>
                    </article>
                </section>

                <section class="min-w-0 rounded-2xl border border-white/10 bg-[#162130] p-4 shadow-xl shadow-slate-950/10 sm:rounded-[2rem] sm:p-5">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">{{ activeTabLabel }}</p>
                        <input v-model="localSearch" type="search" class="h-10 rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none md:w-72" placeholder="Buscar en resultados">
                    </div>

                    <div v-if="['period', 'doctor', 'specialty', 'new_patients'].includes(activeTab)" class="mt-6 grid gap-4">
                        <article v-for="row in rows" :key="rowLabel(row)" class="min-w-0 rounded-2xl border border-white/10 bg-slate-950/30 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-white">{{ rowLabel(row) }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">Total: {{ rowValue(row) }}</p>
                                </div>
                                <p class="text-2xl font-black text-teal-200">{{ rowValue(row) }}</p>
                            </div>
                            <div class="mt-4 h-4 overflow-hidden rounded-full bg-slate-950/70">
                                <div class="h-full rounded-full bg-gradient-to-r from-teal-300 to-cyan-300" :style="{ width: rowPercent(row) }"></div>
                            </div>
                            <div v-if="row.attended !== undefined" class="mt-3 grid gap-2 text-xs font-bold text-slate-400 sm:grid-cols-4">
                                <span>Atendidas: {{ row.attended }}</span>
                                <span>Pendientes: {{ row.pending }}</span>
                                <span>Canceladas: {{ row.cancelled }}</span>
                                <span v-if="row.no_show !== undefined">No asistio: {{ row.no_show }}</span>
                            </div>
                        </article>
                    </div>

                    <div v-else class="mt-6 grid min-w-0 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <article v-for="row in rows" :key="row.appointment_id" class="min-w-0 rounded-2xl border border-white/10 bg-slate-950/30 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-white">{{ row.patient }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ row.document }} - {{ row.date }} {{ row.time }}</p>
                                </div>
                                <span :class="['shrink-0 rounded-full px-3 py-1 text-[11px] font-black', statusClass(row.status)]">{{ row.status }}</span>
                            </div>
                            <div class="mt-4 grid gap-2 text-sm text-slate-300">
                                <p><span class="font-bold text-slate-500">Medico:</span> {{ row.doctor ?? 'No registrado' }}</p>
                                <p><span class="font-bold text-slate-500">Especialidad:</span> {{ row.specialty ?? 'No registrado' }}</p>
                                <p v-if="activeTab === 'patient'"><span class="font-bold text-slate-500">Motivo:</span> {{ row.reason ?? 'No registrado' }}</p>
                                <p v-if="activeTab === 'absences'"><span class="font-bold text-slate-500">Motivo cancelacion:</span> {{ row.cancellation_reason ?? 'No registrado' }}</p>
                            </div>
                        </article>
                    </div>

                    <p v-if="!rows.length" class="py-10 text-center text-sm font-semibold text-slate-500">No existen registros para los filtros seleccionados.</p>
                </section>

                <div v-if="currentPaginator?.links" class="flex flex-col items-start gap-3 px-1 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-2 sm:py-4">
                    <div class="text-sm text-slate-400">Mostrando {{ currentPaginator.from ?? 0 }} - {{ currentPaginator.to ?? 0 }} de {{ currentPaginator.total }}</div>
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <button
                            v-for="link in currentPaginator.links"
                            :key="link.label + (link.url || '')"
                            v-html="translateLabel(link.label)"
                            :disabled="!link.url"
                            @click.prevent="goTo(link.url)"
                            class="rounded-md px-2.5 py-1 text-xs font-bold text-slate-300 hover:bg-white/10 disabled:opacity-40 sm:px-3 sm:text-sm"
                            :class="link.active ? 'bg-white/10' : ''"
                        ></button>
                    </div>
                </div>
            </template>
        </div>
    </DashboardLayout>
</template>
