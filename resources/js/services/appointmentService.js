export const appointmentService = {
    defaultForm() {
        return {
            specialty_id: '',
            patient_id: '',
            doctor_id: '',
            service_id: '',
            appointment_date: '',
            start_time: '',
            end_time: '',
            status: 'SCHEDULED',
        };
    },
};
