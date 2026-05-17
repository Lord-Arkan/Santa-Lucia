<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import AppInput from '@/components/ui/AppInput.vue';
import AppButton from '@/components/ui/AppButton.vue';
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
        <div class="brand-lockup" aria-label="Identidad de marca">
            <small>Clinica</small>
            <strong>Santa Lucia</strong>
        </div>
        <p class="auth-subtitle">Acceso al Sistema Web</p>

        <div v-if="status" class="status-alert" role="status">
            {{ status }}
        </div>

        <form class="form-stack" @submit.prevent="submit">
            <label class="sr-only" for="email">Usuario o correo</label>
            <AppInput
                id="email"
                v-model="form.email"
                type="email"
                icon="user"
                autocomplete="username"
                placeholder="Usuario / Email"
                :error="form.errors.email"
            />

            <label class="sr-only" for="password">Contraseña</label>
            <AppInput
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                icon="lock"
                trailing-icon="toggle"
                autocomplete="current-password"
                placeholder="Contraseña"
                :error="form.errors.password"
                @trailing-icon-click="togglePassword"
            />

            <AppButton type="submit" :disabled="form.processing">
                {{ form.processing ? 'Validando...' : 'Iniciar Sesion' }}
            </AppButton>
        </form>

        <div class="auth-links">
            <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="auth-link"
            >
                Olvide mi contrasena
            </Link>
            <a v-else href="#" class="auth-link" @click.prevent>Olvide mi contrasena</a>

            <Link
                v-if="canRegister"
                :href="route('register')"
                class="auth-link"
            >
                Registrar nuevo usuario
            </Link>
            <a v-else href="#" class="auth-link" @click.prevent>Registrar nuevo usuario</a>
        </div>
    </AuthLayout>
</template>
