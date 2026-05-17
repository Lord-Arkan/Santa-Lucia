export const doctorScheduleService = {
    defaultForm() {
        return {
            doctor_id: '',
            day_of_week: '',
            start_time: '',
            end_time: '',
            status: 'activo',
        };
    },
};
