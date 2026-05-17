const dashboardSnapshot = {
    summary: [
        {
            id: 'overdue',
            variant: 'kpi-red',
            count: 15,
            title: 'Citas Vencidas',
            description: 'Citas que requieren revision hoy',
        },
        {
            id: 'pending',
            variant: 'kpi-amber',
            count: 42,
            title: 'Citas Pendientes',
            description: 'Citas pendientes de confirmacion',
        },
        {
            id: 'total',
            variant: 'kpi-green',
            count: 124,
            title: 'Citas Totales',
            description: 'Citas programadas para la semana',
        },
    ],
    upcomingAppointments: [
        '09:00 AM - Juan Perez',
        '10:30 AM - Maria Gomez',
        '12:00 PM - Dr. Garcia',
        '02:00 PM - Lucia Soto',
    ],
    schedule: {
        days: ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
        rows: [
            {
                time: '09:00',
                events: ['Juan Perez', '', '', '', 'Ana Torres', ''],
            },
            {
                time: '10:30',
                events: ['', 'Maria Gomez', 'Maria Gomez', '', '', ''],
            },
            {
                time: '12:00',
                events: ['Dr. Garcia', '', '', 'Carlos Ruiz', '', ''],
            },
            {
                time: '14:00',
                events: ['', 'Lucia Soto', '', 'Lucia Soto', '', ''],
            },
            {
                time: '16:30',
                events: ['', '', 'Rosa Medina', '', 'Dr. Rivera', ''],
            },
        ],
    },
};

export const dashboardService = {
    getDashboardSnapshot() {
        return dashboardSnapshot;
    },
};
