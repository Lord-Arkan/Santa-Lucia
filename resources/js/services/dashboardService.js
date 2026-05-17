const dashboardSnapshot = {
    summary: [
        {
            id: 'overdue',
            variant: 'kpi-red',
            count: 15,
            title: 'Citas Vencidas',
            description: 'Citas vencidas hoy',
        },
        {
            id: 'pending',
            variant: 'kpi-amber',
            count: 42,
            title: 'Citas Pendientes',
            description: 'Citas pendientes de confirmación',
        },
        {
            id: 'total',
            variant: 'kpi-green',
            count: 124,
            title: 'Citas Totales',
            description: 'Citas totales programadas hoy',
        },
    ],
    upcomingAppointments: [
        '09:00 AM - Juan Pérez',
        '10:30 AM - Maria Gomez',
        '12:00 PM - Dr. Garcia',
        '02:00 PM - Lucia Soto',
    ],
    schedule: {
        days: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        rows: [
            {
                time: '09:00',
                events: ['Juan Pérez', '', '', '', '', ''],
            },
            {
                time: '10:30',
                events: ['', 'Maria Gomez', 'Maria Gomez', '', '', ''],
            },
            {
                time: '12:00',
                events: ['Dr. Garcia', '', '', '', '', ''],
            },
            {
                time: '14:00',
                events: ['', 'Lucia Soto', '', 'Lucia Soto', '', ''],
            },
        ],
    },
};

export const dashboardService = {
    getDashboardSnapshot() {
        return dashboardSnapshot;
    },
};
