<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useLoginForm } from '@/composables/useLoginForm';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: '',
    },
});

const showPassword = ref(false);
const { form, submit } = useLoginForm();

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <Head title="Acceso" />

    <AuthLayout>
        <div class="w-full">
            <div class="mb-7 max-w-sm">
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-teal-600">Portal medico</p>
                <h2 class="mt-3 text-4xl font-black leading-tight text-slate-900">Bienvenido de nuevo</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Ingresa con tus credenciales para continuar gestionando la atencion de Santa Lucia.
                </p>
            </div>

            <div v-if="status" class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" role="status">
                {{ status }}
            </div>

            <form class="max-w-[340px] rounded-[1.75rem] bg-gradient-to-br from-teal-500 via-cyan-600 to-blue-800 p-5 text-white shadow-2xl shadow-teal-900/25 sm:p-6" @submit.prevent="submit">
                <div class="mb-5 text-center">
                    <p class="text-2xl font-black">Sign In</p>
                    <p class="mt-1 text-xs text-white/75">Acceso seguro al sistema clinico</p>
                </div>

                <div class="space-y-3">
                    <label class="block" for="email">
                        <span class="sr-only">Correo electronico</span>
                        <span class="relative block">
                            <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-teal-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.97 0-9 2.24-9 5v1h18v-1c0-2.76-4.03-5-9-5Z" />
                            </svg>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="username"
                                placeholder="Correo electronico"
                                class="h-12 w-full rounded-xl border-0 bg-white pl-11 pr-4 text-sm font-medium text-slate-700 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-white/35"
                            >
                        </span>
                    </label>
                    <p v-if="form.errors.email" class="-mt-1 text-xs font-semibold text-rose-100">{{ form.errors.email }}</p>

                    <label class="block" for="password">
                        <span class="sr-only">Password</span>
                        <span class="relative block">
                            <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-teal-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M17 8h-1V6a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2Zm-3 0h-4V6a2 2 0 1 1 4 0Z" />
                            </svg>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="Password"
                                class="h-12 w-full rounded-xl border-0 bg-white pl-11 pr-12 text-sm font-medium text-slate-700 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-white/35"
                            >
                            <button
                                type="button"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-teal-700"
                                @click="togglePassword"
                            >
                                <span class="sr-only">Mostrar u ocultar password</span>
                                <svg v-if="showPassword" class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 5c5 0 9.27 3.11 11 7.5C21.27 16.89 17 20 12 20S2.73 16.89 1 12.5C2.73 8.11 7 5 12 5Zm0 3a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Zm0 2a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Z" />
                                </svg>
                                <svg v-else class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="m2 4 2-2 18 18-2 2-3.7-3.7A12.7 12.7 0 0 1 12 19c-5 0-9.27-3.11-11-7.5a12.89 12.89 0 0 1 4.2-5.59Zm7.78 7.78a3 3 0 0 0 4.44 4.44ZM12 5a9.9 9.9 0 0 1 11 6.5 12.5 12.5 0 0 1-3.12 4.65L16.9 13.2A5 5 0 0 0 10.8 7.1L8.9 5.2A10.58 10.58 0 0 1 12 5Z" />
                                </svg>
                            </button>
                        </span>
                    </label>
                    <p v-if="form.errors.password" class="-mt-1 text-xs font-semibold text-rose-100">{{ form.errors.password }}</p>
                </div>

                <div class="mt-4 flex items-center justify-between gap-3 text-[11px]">
                    <label class="inline-flex items-center gap-2 text-white/85">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="size-3.5 rounded border-white/60 bg-white/15 text-teal-500 focus:ring-2 focus:ring-white/50"
                        >
                        Remember me
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="font-semibold text-white/90 underline-offset-4 hover:text-white hover:underline"
                    >
                        Forgot password?
                    </Link>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="mt-6 h-12 w-full rounded-full bg-white px-5 text-sm font-black text-teal-700 shadow-lg shadow-teal-950/20 transition hover:-translate-y-0.5 hover:bg-cyan-50 focus:outline-none focus:ring-4 focus:ring-white/35 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    {{ form.processing ? 'Validando...' : 'Log In' }}
                </button>

                <p class="mt-5 text-center text-[11px] text-white/80">
                    Cant access your account?
                    <Link v-if="canRegister" :href="route('register')" class="font-bold text-white underline-offset-4 hover:underline">Sign up</Link>
                    <span v-else class="font-bold text-white">Contacta al administrador</span>
                </p>
            </form>
        </div>
    </AuthLayout>
</template>
