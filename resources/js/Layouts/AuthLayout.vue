<script setup>
defineProps({
    imageUrl: {
        type: String,
        default: 'https://images.pexels.com/photos/5207105/pexels-photo-5207105.jpeg?auto=compress&cs=tinysrgb&w=1200&q=80',
    },
});
</script>

<template>
    <div class="auth-page">
        <section class="auth-shell" aria-label="Acceso al sistema">
            <aside class="auth-visual">
                <div class="auth-brand-corner">
                    <small>Clínica</small>
                    <strong>Santa Lucia</strong>
                </div>
                <img
                    :src="imageUrl"
                    alt="Recepción clínica"
                    width="1200"
                    height="1600"
                    loading="eager"
                    decoding="async"
                >
            </aside>

            <div class="auth-panel">
                <slot />
            </div>
        </section>
    </div>
</template>
