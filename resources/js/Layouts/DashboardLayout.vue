<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';
import { authService } from '@/services/authService';
import Dropdown from '@/Components/Dropdown.vue';
import Banner from '@/Components/Banner.vue';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
});

const { user } = useAuth();
const isSidebarCollapsed = ref(false);
const isSettingsOpen = ref(false);

const userLabel = computed(() => user.value?.name ?? 'Usuario');
const userRole = computed(() => user.value?.rol ?? 'asistente');
const userPhotoUrl = computed(() => (user.value?.profile_photo_path ? user.value.profile_photo_url : ''));
const userInitials = computed(() => {
    const name = userLabel.value.trim().split(' ').filter(Boolean);

    if (name.length === 0) {
        return 'US';
    }

    return name.slice(0, 2).map((part) => part.charAt(0)).join('').toUpperCase();
});

const navItems = [
    { label: 'Inicio', routeName: 'dashboard', icon: 'M4 5.5A1.5 1.5 0 0 1 5.5 4h4A1.5 1.5 0 0 1 11 5.5v4A1.5 1.5 0 0 1 9.5 11h-4A1.5 1.5 0 0 1 4 9.5v-4Zm9 0A1.5 1.5 0 0 1 14.5 4h4A1.5 1.5 0 0 1 20 5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 13 9.5v-4Zm-9 9A1.5 1.5 0 0 1 5.5 13h4a1.5 1.5 0 0 1 1.5 1.5v4A1.5 1.5 0 0 1 9.5 20h-4A1.5 1.5 0 0 1 4 18.5v-4Zm9 0a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a1.5 1.5 0 0 1-1.5-1.5v-4Z' },
    { label: 'Citas', routeName: 'appointments.index', icon: 'M7 2v3M17 2v3M4 8h16M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm4 7h3v3H9v-3Z' },
    { label: 'Servicios', routeName: 'services.index', icon: 'M12 3 4 7v10l8 4 8-4V7l-8-4Zm0 0v18M4 7l8 4 8-4' },
    { label: 'Pacientes', routeName: 'patients.index', icon: 'M8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-1a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM2 20a6 6 0 0 1 12 0v1H2v-1Zm12.5 1v-1a7.5 7.5 0 0 0-2.1-5.2A5 5 0 0 1 22 18v3h-7.5Z' },
    { label: 'Horarios', routeName: 'doctor-schedules.index', icon: 'M12 8v5l3 3' },
    { label: 'Doctores', routeName: 'doctors.index', icon: 'M12 2a5 5 0 0 0-5 5v2H5a2 2 0 0 0-2 2v8a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3v-8a2 2 0 0 0-2-2h-2V7a5 5 0 0 0-5-5Zm-3 7V7a3 3 0 1 1 6 0v2H9Zm2 5h2v2h2v2h-2v2h-2v-2H9v-2h2v-2Z' },
    { label: 'Historial', routeName: 'dashboard', icon: 'M5 3h11l3 3v15H5V3Zm10 0v4h4M8 11h8M8 15h8M8 19h5' },
    { label: 'Reportes', routeName: 'dashboard', icon: 'M5 19V5h14v14H5Zm4-3V9m4 7v-5m4 5v-8' },
];

const settingsItems = [
    { label: 'Usuarios', routeName: 'users.index', icon: 'M8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-1a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM2 20a6 6 0 0 1 12 0v1H2v-1Zm12.5 1v-1a7.5 7.5 0 0 0-2.1-5.2A5 5 0 0 1 22 18v3h-7.5Z' },
];

const isRouteActive = (routeName) => {
    try {
        return route().current(routeName);
    } catch {
        return false;
    }
};

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const logout = () => {
    authService.logout();
};

onMounted(() => {
    isSidebarCollapsed.value = window.localStorage.getItem('santa-lucia-sidebar-collapsed') === 'true';

    // Restaurar estado de configuración si el usuario lo guardó;
    // no abrir automáticamente al navegar entre opciones.
    try {
        const stored = window.localStorage.getItem('santa-lucia-settings-open');
        if (stored !== null) {
            isSettingsOpen.value = stored === 'true';
        }
    } catch (e) {
        // ignorar en entornos sin window
    }
});

// Cerrar la sección "Configuracion" automáticamente cuando la ruta cambie
// a una que no pertenezca a settings.
const page = usePage();
watch(() => page.url, () => {
    try {
        const activeSettings = settingsItems.some((i) => isRouteActive(i.routeName));
        if (!activeSettings) {
            isSettingsOpen.value = false;
        }
    } catch (e) {
        // ignore
    }
});

watch(isSidebarCollapsed, (value) => {
    window.localStorage.setItem('santa-lucia-sidebar-collapsed', String(value));
});

watch(isSettingsOpen, (value) => {
    window.localStorage.setItem('santa-lucia-settings-open', String(value));
});
</script>

<template>
    <div
        class="min-h-screen bg-slate-950 text-slate-100 transition-[grid-template-columns] duration-300 lg:grid"
        :class="isSidebarCollapsed ? 'lg:grid-cols-[88px_1fr]' : 'lg:grid-cols-[292px_1fr]'"
    >
        <aside class="relative hidden border-r border-white/10 bg-[#0f1c27] lg:block">
            <div class="flex h-full flex-col">
                <!-- Área clicable en el margen derecho del sidebar para acoplar/desacoplar (click en cualquier parte del borde) -->
                <button
                    type="button"
                    @click="toggleSidebar"
                    :title="isSidebarCollapsed ? 'Expandir menu' : 'Colapsar menu'"
                    aria-label="Alternar sidebar"
                    class="absolute right-0 top-0 h-full w-6 z-40 hidden lg:flex items-center justify-center"
                >
                    <span class="grid size-9 place-items-center rounded-full border border-white/10 bg-[#0f1c27]/80 text-slate-300 hover:bg-white/10 transition translate-x-1/2">
                        <svg viewBox="0 0 24 24" class="size-5 transition-transform" :class="isSidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" aria-hidden="true">
                            <path d="m15 5 1.4 1.4L10.8 12l5.6 5.6L15 19l-7-7 7-7Z" />
                        </svg>
                    </span>
                </button>
                <div class="px-4 pb-6 pt-8">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div v-if="!isSidebarCollapsed" class="grid size-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-teal-400 to-cyan-500 shadow-lg shadow-cyan-500/20">
                                <svg viewBox="0 0 48 48" class="size-7 text-white" aria-hidden="true">
                                    <path fill="currentColor" d="M18 6h12v12h12v12H30v12H18V30H6V18h12z" />
                                </svg>
                            </div>
                            <div v-if="!isSidebarCollapsed" class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-[0.28em] text-teal-300">Clinica</p>
                                <p class="truncate text-2xl font-black leading-none text-white">Santa Lucia</p>
                            </div>
                        </div>

                        <div></div>
                    </div>
                </div>

                <nav class="min-h-0 flex-1 overflow-y-auto px-4 pb-6" aria-label="Menu principal">
                    <p v-if="!isSidebarCollapsed" class="px-3 text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Menu</p>

                    

                    <ul class="mt-4 space-y-1.5">
                        <li v-for="item in navItems" :key="item.label">
                            <Link
                                    :href="route(item.routeName)"
                                    @click="isSettingsOpen = false"
                                    class="group flex items-center rounded-2xl px-3 py-3 text-sm font-bold transition"
                                    :class="[
                                        isSidebarCollapsed ? 'justify-center' : 'justify-between',
                                        isRouteActive(item.routeName) ? 'bg-teal-400/15 text-white shadow-inner shadow-white/5 ring-1 ring-teal-300/20' : 'text-slate-300 hover:bg-white/10 hover:text-white',
                                    ]"
                                    :title="isSidebarCollapsed ? item.label : null"
                                >
                                <span class="flex items-center gap-3">
                                    <span class="grid size-9 place-items-center rounded-xl border border-white/10 bg-white/5 text-slate-400 transition group-hover:text-teal-200">
                                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" aria-hidden="true">
                                            <path :d="item.icon" />
                                        </svg>
                                    </span>
                                    <span v-if="!isSidebarCollapsed">{{ item.label }}</span>
                                </span>
                            </Link>
                        </li>
                    </ul>

                    <!-- Línea separadora entre Reportes y Configuracion -->
                    <div class="mt-6">
                        <div class="mx-3 border-t border-white/10"></div>
                    </div>

                    <div class="mt-7">
                        <button
                            class="flex w-full items-center rounded-2xl px-3 py-3 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white"
                            :class="isSidebarCollapsed ? 'justify-center' : 'justify-between'"
                            type="button"
                            title="Configuracion"
                            @click="isSettingsOpen = !isSettingsOpen"
                        >
                            <span class="flex items-center gap-3">
                                <span class="grid size-9 place-items-center rounded-xl border border-white/10 bg-white/5 text-slate-400">
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" aria-hidden="true">
                                        <path d="M12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm8.2 4a7.8 7.8 0 0 0-.1-1l2-1.5-2-3.5-2.4 1a8 8 0 0 0-1.7-1L15.7 3h-4l-.3 3a8 8 0 0 0-1.7 1L7.3 6l-2 3.5 2 1.5a7.8 7.8 0 0 0 0 2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.3 3h4l.3-3a8 8 0 0 0 1.7-1l2.4 1 2-3.5-2-1.5c.1-.3.1-.7.1-1Z" />
                                    </svg>
                                </span>
                                <span v-if="!isSidebarCollapsed">Configuracion</span>
                            </span>
                            <svg v-if="!isSidebarCollapsed" viewBox="0 0 20 20" class="size-4 text-slate-500 transition" :class="isSettingsOpen ? 'rotate-90' : ''" fill="currentColor" aria-hidden="true">
                                <path d="M7.5 5.5 12 10l-4.5 4.5 1.1 1.1 5.6-5.6-5.6-5.6-1.1 1.1Z" />
                            </svg>
                        </button>

                        <ul v-show="isSettingsOpen" class="mt-2 space-y-1">
                            <li v-for="item in settingsItems" :key="item.label">
                                <Link
                                    :href="route(item.routeName)"
                                    class="flex items-center rounded-2xl py-2.5 text-sm font-bold transition"
                                    :class="[
                                        isSidebarCollapsed ? 'justify-center px-3' : 'gap-3 px-5 pl-14',
                                        isRouteActive(item.routeName) ? 'bg-cyan-400/15 text-white ring-1 ring-cyan-300/20' : 'text-slate-400 hover:bg-white/10 hover:text-white',
                                    ]"
                                    :title="isSidebarCollapsed ? item.label : null"
                                >
                                    <span v-if="isSidebarCollapsed" class="grid size-9 place-items-center rounded-xl border border-white/10 bg-white/5">
                                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" aria-hidden="true">
                                            <path :d="item.icon" />
                                        </svg>
                                    </span>
                                    <span v-else>{{ item.label }}</span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </aside>

        <section class="min-w-0 bg-[#101824]">
            <header class="sticky top-0 z-20 border-b border-white/10 bg-[#101824]/90 px-4 py-4 backdrop-blur sm:px-6 lg:px-8">
                <div class="flex items-center gap-4 justify-between flex-nowrap">
                    <div class="flex items-center gap-3 flex-nowrap">
                        <div v-if="isSidebarCollapsed" class="hidden lg:grid size-11 place-items-center rounded-2xl bg-gradient-to-br from-teal-400 to-cyan-500 shadow-lg shadow-cyan-500/20 mr-2" aria-hidden="true">
                            <svg viewBox="0 0 48 48" class="size-7 text-white" aria-hidden="true">
                                <path fill="currentColor" d="M18 6h12v12h12v12H30v12H18V30H6V18h12z" />
                            </svg>
                        </div>

                        <div class="grid size-11 place-items-center rounded-2xl border border-white/10 bg-white/5 text-teal-200 lg:hidden" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true">
                                <path d="M18 4h4v4h-4v4h-4V8h-4V4h4V0h4v4ZM4 9h8v2H4V9Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-teal-300">Santa Lucia</p>
                            <h1 v-if="props.title" class="mt-1 text-2xl font-black text-white sm:text-3xl">{{ props.title }}</h1>
                        </div>

                        
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative w-full sm:w-[360px]">
                            <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="m21 20-5.2-5.2a7 7 0 1 0-1 1L20 21l1-1Zm-11-6a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z" />
                            </svg>
                            <input
                                type="search"
                                placeholder="Buscar paciente, cita o doctor..."
                                class="h-12 w-full rounded-2xl border border-white/10 bg-white/5 pl-12 pr-4 text-sm font-medium text-white outline-none transition placeholder:text-slate-500 focus:border-teal-300/60 focus:ring-4 focus:ring-teal-400/10"
                            >
                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/5 px-3 py-2">
                            <img
                                v-if="userPhotoUrl"
                                :src="userPhotoUrl"
                                :alt="userLabel"
                                class="size-10 rounded-full object-cover ring-2 ring-teal-300/30"
                            >
                            <span v-else class="grid size-10 place-items-center rounded-full bg-gradient-to-br from-teal-400 to-cyan-500 text-sm font-black text-white">{{ userInitials }}</span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold text-white">{{ userLabel }}</span>
                                <span class="block text-[11px] font-bold uppercase tracking-[0.12em] text-teal-200">{{ userRole }}</span>
                            </span>

                            <Dropdown align="right" width="40" :content-classes="['py-1','bg-[#0f1c27]','rounded-md']">
                                <template #trigger>
                                    <button class="rounded-xl px-3 py-2 text-xs font-bold text-teal-200 transition hover:bg-white/10 hover:text-white" type="button" aria-label="Abrir menú de usuario">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <button class="w-full text-left px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/5 whitespace-nowrap min-w-max" @click="logout">Cerrar sesión</button>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>

                <nav class="mt-4 flex gap-2 overflow-x-auto pb-1 lg:hidden" aria-label="Menu principal movil">
                    <Link
                        v-for="item in [...navItems, ...settingsItems]"
                        :key="item.label"
                        :href="route(item.routeName)"
                        class="shrink-0 rounded-2xl border px-4 py-2 text-xs font-black"
                        :class="isRouteActive(item.routeName) ? 'border-teal-300/30 bg-teal-400/15 text-white' : 'border-white/10 bg-white/5 text-slate-300'"
                    >
                        {{ item.label }}
                    </Link>
                </nav>
            </header>

            <!-- Global toast / banner -->
            <Banner />

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>
        </section>
    </div>
</template>
