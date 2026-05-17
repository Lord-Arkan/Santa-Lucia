<script setup>
defineProps({
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
</script>

<template>
    <section class="board" aria-label="Calendario de citas">
        <h4>{{ title }}</h4>
        <div class="board-grid">
            <div>
                <h5>Próximas Citas:</h5>
                <ul class="upcoming-list">
                    <li v-for="appointment in upcomingAppointments" :key="appointment">
                        {{ appointment }}
                    </li>
                </ul>
            </div>

            <div class="schedule-wrap">
                <div class="schedule-head">
                    <span>Hora</span>
                    <span v-for="day in schedule.days" :key="day">{{ day }}</span>
                </div>

                <div class="schedule-row" v-for="row in schedule.rows" :key="row.time">
                    <span>{{ row.time }}</span>
                    <span v-for="(event, index) in row.events" :key="`${row.time}-${index}`">
                        <small v-if="event" class="schedule-pill" :class="{ 'is-muted': event.includes('Lucia') }">{{ event }}</small>
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
