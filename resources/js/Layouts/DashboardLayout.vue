<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';
import { authService } from '@/services/authService';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const { user } = useAuth();

const userLabel = computed(() => user.value?.name ?? 'Usuario');
const userInitials = computed(() => {
    const name = userLabel.value.trim().split(' ').filter(Boolean);
    if (name.length === 0) {
        return 'US';
    }

    return name.slice(0, 2).map((part) => part.charAt(0)).join('').toUpperCase();
});

const navItems = [
    { label: 'Inicio', routeName: 'dashboard' },
    { label: 'Citas', routeName: 'dashboard' },
    { label: 'Servicios', routeName: 'dashboard' },
    { label: 'Pacientes', routeName: 'dashboard' },
    { label: 'Doctores', routeName: 'dashboard' },
    { label: 'Historial', routeName: 'dashboard' },
    { label: 'Reportes', routeName: 'dashboard' },
    { label: 'Configuración', routeName: 'dashboard' },
];

const isCurrentRoute = (routeName) => {
    if (typeof route !== 'function') {
        return false;
    }

    return route().current(routeName);
};

const logout = () => {
    authService.logout();
};
</script>

<template>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <small>Clinica</small>
                <strong>Santa Lucia</strong>
            </div>

            <nav aria-label="Menú principal">
                <ul class="nav-list">
                    <li v-for="item in navItems" :key="item.label">
                        <Link
                            :href="route(item.routeName)"
                            class="nav-item"
                            :class="{ 'is-active': isCurrentRoute(item.routeName) && item.label === 'Inicio' }"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <circle cx="12" cy="12" r="7" />
                            </svg>
                            <span>{{ item.label }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>
        </aside>

        <section class="main-content">
            <header class="topbar">
                <input type="search" class="search-input" placeholder="Buscar">

                <div class="topbar-user">
                    <span class="avatar" aria-hidden="true">{{ userInitials }}</span>
                    <span>{{ userLabel }}</span>
                    <button class="logout-link" type="button" @click="logout">Logout</button>
                </div>
            </header>

            <main class="dashboard">
                <h1 class="dashboard-title">{{ props.title }}</h1>
                <slot />
            </main>
        </section>
    </div>
</template>
