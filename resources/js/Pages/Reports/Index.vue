<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

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
    { key: 'dashboard', label: 'General' },
    { key: 'period', label: 'Por periodo' },
    { key: 'doctor', label: 'Por medico' },
    { key: 'specialty', label: 'Especialidades' },
    { key: 'patient', label: 'Historial paciente' },
    { key: 'absences', label: 'Inasistencias' },
    { key: 'new_patients', label: 'Pacientes nuevos' },
];

const activeTab = ref('dashboard');
const showFilters = ref(false);
const localSearch = ref('');
const filters = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    group_by: props.filters.group_by,
    doctor_id: props.filters.doctor_id,
    specialty_id: props.filters.specialty_id,
    patient_search: props.filters.patient_search,
});

const kpis = computed(() => [
    ['Total citas', props.summary.total_appointments],
    ['Citas de hoy', props.summary.today_appointments],
    ['Pendientes', props.summary.pending_appointments],
    ['Atendidas', props.summary.attended_appointments],
    ['Canceladas', props.summary.cancelled_appointments],
    ['Pacientes atendidos', props.summary.attended_patients],
    ['Medico top', `${props.summary.top_doctor} (${props.summary.top_doctor_total})`],
    ['Especialidad top', `${props.summary.top_specialty} (${props.summary.top_specialty_total})`],
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

const activeTabLabel = computed(() => tabs.find((tab) => tab.key === activeTab.value)?.label ?? '');

const cleanFilters = () => Object.fromEntries(Object.entries(filters.value).filter(([, value]) => value !== '' && value !== null && value !== undefined));

const applyFilters = () => {
    router.get(route('reports.index'), cleanFilters(), { preserveState: true, replace: true });
};

const clearFilters = () => {
    filters.value = {
        start_date: '',
        end_date: '',
        group_by: 'day',
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

const setTab = (tab) => {
    activeTab.value = tab;
    localSearch.value = '';
};

const rowLabel = (row) => row.period ?? row.doctor ?? row.specialty ?? row.patient ?? 'Registro';
const rowValue = (row) => Number(row.total ?? 1);
const rowPercent = (row) => `${Math.max((rowValue(row) / maxRows.value) * 100, 4)}%`;

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
        <div class="grid gap-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <button type="button" class="rounded-2xl bg-white/5 px-3 py-2 text-sm font-bold text-slate-300 hover:bg-white/10" @click="showFilters = !showFilters">
                    {{ showFilters ? 'Ocultar' : 'Filtros' }}
                </button>

                <div class="flex flex-wrap gap-2">
                    <button type="button" class="h-10 rounded-2xl border border-emerald-300/20 px-4 text-sm font-black text-emerald-200" @click="exportReport('csv')">CSV</button>
                    <button type="button" class="h-10 rounded-2xl border border-sky-300/20 px-4 text-sm font-black text-sky-200" @click="exportReport('xlsx')">Excel</button>
                    <button type="button" class="h-10 rounded-2xl border border-rose-300/20 px-4 text-sm font-black text-rose-200" @click="exportReport('pdf')">PDF</button>
                    <button type="button" class="h-10 rounded-2xl border border-white/10 px-4 text-sm font-black text-slate-300" @click="printReport">Imprimir</button>
                </div>
            </div>

            <transition name="fade">
                <section v-show="showFilters" class="rounded-[2rem] border border-white/10 bg-[#162130] p-5 shadow-xl shadow-slate-950/10">
                    <form class="grid gap-3 lg:grid-cols-6" @submit.prevent="applyFilters">
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Inicio</span>
                            <input v-model="filters.start_date" type="date" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none">
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Fin</span>
                            <input v-model="filters.end_date" type="date" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none">
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Agrupar</span>
                            <select v-model="filters.group_by" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                                <option value="day">Dia</option>
                                <option value="week">Semana</option>
                                <option value="month">Mes</option>
                            </select>
                        </label>
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

            <div class="flex gap-2 overflow-x-auto pb-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="shrink-0 rounded-2xl border px-4 py-2 text-xs font-black transition"
                    :class="activeTab === tab.key ? 'border-teal-300/30 bg-teal-400/15 text-white' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'"
                    @click="setTab(tab.key)"
                >
                    {{ tab.label }}
                </button>
            </div>

            <template v-if="activeTab === 'dashboard'">
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article v-for="[label, value] in kpis" :key="label" class="rounded-2xl border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">{{ label }}</p>
                        <p class="mt-3 text-2xl font-black text-white">{{ value }}</p>
                    </article>
                </section>

                <section class="grid gap-4 xl:grid-cols-3">
                    <article class="rounded-[2rem] border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Estados</p>
                        <div class="mt-5 grid gap-3">
                            <div v-for="item in props.charts.status" :key="item.label">
                                <div class="mb-1 flex justify-between text-xs font-bold text-slate-400"><span>{{ item.label }}</span><span>{{ item.value }}</span></div>
                                <div class="h-3 overflow-hidden rounded-full bg-slate-950/60"><div class="h-full rounded-full bg-teal-300" :style="{ width: `${(item.value / maxStatus) * 100}%` }"></div></div>
                            </div>
                            <p v-if="!props.charts.status.length" class="py-8 text-center text-sm font-semibold text-slate-500">Sin registros.</p>
                        </div>
                    </article>

                    <article class="rounded-[2rem] border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Periodo</p>
                        <div class="mt-5 flex h-52 items-end gap-2 overflow-x-auto">
                            <div v-for="item in props.charts.period" :key="item.period" class="flex min-w-10 flex-1 flex-col items-center gap-2">
                                <div class="w-full rounded-t-xl bg-cyan-300/80" :style="{ height: `${Math.max((item.total / maxPeriod) * 180, 6)}px` }"></div>
                                <span class="max-w-20 truncate text-[10px] font-bold text-slate-500">{{ item.period }}</span>
                            </div>
                            <p v-if="!props.charts.period.length" class="w-full py-8 text-center text-sm font-semibold text-slate-500">Sin registros.</p>
                        </div>
                    </article>

                    <article class="rounded-[2rem] border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Especialidades</p>
                        <div class="mt-5 grid gap-3">
                            <div v-for="item in props.charts.specialties" :key="item.specialty">
                                <div class="mb-1 flex justify-between text-xs font-bold text-slate-400"><span class="truncate">{{ item.specialty }}</span><span>{{ item.total }}</span></div>
                                <div class="h-3 overflow-hidden rounded-full bg-slate-950/60"><div class="h-full rounded-full bg-sky-300" :style="{ width: `${(item.total / maxSpecialty) * 100}%` }"></div></div>
                            </div>
                            <p v-if="!props.charts.specialties.length" class="py-8 text-center text-sm font-semibold text-slate-500">Sin registros.</p>
                        </div>
                    </article>
                </section>
            </template>

            <template v-else>
                <section v-if="activeTab === 'absences'" class="grid gap-4 sm:grid-cols-3">
                    <article class="rounded-2xl border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Citas evaluadas</p>
                        <p class="mt-3 text-2xl font-black text-white">{{ props.absenceReport.stats.total }}</p>
                    </article>
                    <article class="rounded-2xl border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Canceladas / no asistio</p>
                        <p class="mt-3 text-2xl font-black text-white">{{ props.absenceReport.stats.absences }}</p>
                    </article>
                    <article class="rounded-2xl border border-white/10 bg-[#162130] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Ausentismo</p>
                        <p class="mt-3 text-2xl font-black text-white">{{ props.absenceReport.stats.rate }}%</p>
                    </article>
                </section>

                <section class="rounded-[2rem] border border-white/10 bg-[#162130] p-5 shadow-xl shadow-slate-950/10">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-300">{{ activeTabLabel }}</p>
                        <input v-model="localSearch" type="search" class="h-10 rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none md:w-72" placeholder="Buscar en resultados">
                    </div>

                    <div v-if="['period', 'doctor', 'specialty', 'new_patients'].includes(activeTab)" class="mt-6 grid gap-4">
                        <article v-for="row in rows" :key="rowLabel(row)" class="rounded-2xl border border-white/10 bg-slate-950/30 p-4">
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

                    <div v-else class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <article v-for="row in rows" :key="row.appointment_id" class="rounded-2xl border border-white/10 bg-slate-950/30 p-4">
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
            </template>
        </div>
    </DashboardLayout>
</template>
