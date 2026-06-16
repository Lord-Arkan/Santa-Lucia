<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { toRelativeUrl } from '@/utils/navigation';

const props = defineProps({
    patients: { type: Object, required: true },
});

const page = usePage();
const showFilters = ref(false);
const filters = ref({
    search: page.props.filters?.search ?? '',
    status: page.props.filters?.status ?? '',
});

const applyFilters = () => {
    router.get(route('history.index'), {
        search: filters.value.search || undefined,
        status: filters.value.status || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    filters.value.search = '';
    filters.value.status = '';
    router.get(route('history.index'), {}, { preserveState: true, replace: true });
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
</script>

<template>
    <Head title="Historial clinico" />

    <DashboardLayout title="Historial clinico">
        <div class="grid gap-6">
            <div class="flex items-center justify-between">
                <button type="button" class="rounded-2xl bg-white/5 px-3 py-2 text-sm font-bold text-slate-300 hover:bg-white/10" @click="showFilters = !showFilters">
                    {{ showFilters ? 'Ocultar' : 'Filtros' }}
                </button>
            </div>

            <transition name="fade">
                <div v-show="showFilters" class="rounded-2xl border border-white/10 bg-[#0f1c27] p-4">
                    <form class="grid gap-3 sm:grid-cols-4" @submit.prevent="applyFilters">
                        <label class="block sm:col-span-2">
                            <span class="block text-xs font-bold text-slate-400">Paciente</span>
                            <input v-model="filters.search" type="search" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-white/5 px-3 text-sm text-white outline-none" placeholder="Nombre, DNI o codigo">
                        </label>
                        <label class="block">
                            <span class="block text-xs font-bold text-slate-400">Estado</span>
                            <select v-model="filters.status" class="mt-2 h-10 w-full rounded-2xl border border-white/10 bg-[#101824] px-3 text-sm text-white outline-none">
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </label>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="h-10 rounded-2xl bg-teal-400/80 px-4 text-sm font-black text-slate-950">Aplicar</button>
                            <button type="button" class="h-10 rounded-2xl border border-white/10 px-4 text-sm font-black text-slate-300" @click="clearFilters">Limpiar</button>
                        </div>
                    </form>
                </div>
            </transition>

            <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[#162130]">
                <div class="border-b border-white/10 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-300">Pacientes autorizados</p>
                </div>
                <div v-if="props.patients.data.length" class="divide-y divide-white/10">
                    <article v-for="patient in props.patients.data" :key="patient.patient_id" class="flex flex-wrap items-center justify-between gap-4 p-5">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="font-black text-white">{{ patient.name }}</p>
                                <span :class="['rounded-full px-3 py-1 text-[11px] font-black', patient.status === 'activo' ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200']">
                                    {{ patient.status === 'activo' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-slate-400">{{ patient.document }}</p>
                            <p class="mt-2 text-xs font-semibold text-slate-500">{{ patient.appointments_count }} citas - {{ patient.clinical_records_count }} registros clinicos</p>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="route('patients.history', patient.patient_id)" class="rounded-xl border border-sky-300/20 px-3 py-2 text-xs font-black text-sky-200 hover:bg-sky-400/10">Historial</Link>
                            <Link :href="route('patients.records.index', patient.patient_id)" class="rounded-xl border border-violet-300/20 px-3 py-2 text-xs font-black text-violet-200 hover:bg-violet-400/10">Registros</Link>
                        </div>
                    </article>
                </div>
                <p v-else class="p-6 text-sm text-slate-400">No hay pacientes relacionados disponibles.</p>
            </section>

            <div v-if="props.patients.links" class="flex items-center justify-between px-2 py-4">
                <div class="text-sm text-slate-400">Mostrando {{ props.patients.from ?? 0 }} - {{ props.patients.to ?? 0 }} de {{ props.patients.total }}</div>
                <div class="flex items-center gap-2">
                    <button
                        v-for="link in props.patients.links"
                        :key="link.label + (link.url || '')"
                        v-html="translateLabel(link.label)"
                        :disabled="!link.url"
                        @click.prevent="goTo(link.url)"
                        class="rounded-md px-3 py-1 text-sm font-bold text-slate-300 hover:bg-white/10 disabled:opacity-40"
                        :class="link.active ? 'bg-white/10' : ''"
                    ></button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
