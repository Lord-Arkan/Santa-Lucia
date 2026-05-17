<script setup>
defineProps({
    item: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <article class="kpi-card" :class="item.variant">
        <h3>{{ item.count }}</h3>
        <div>
            <p><strong>{{ item.title }}</strong></p>
            <p>{{ item.description }}</p>
        </div>
    </article>
</template>
