export const serviceService = {
    defaultForm() {
        return {
            name: '',
            description: '',
            price: '',
            duration_minutes: '',
            status: 'activo',
            specialty_ids: [],
            doctor_ids: [],
        };
    },
};
