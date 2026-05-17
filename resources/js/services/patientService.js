export const patientService = {
    defaultForm() {
        return {
            document_type: 'DNI',
            document_number: '',
            first_name: '',
            last_name: '',
            birth_date: '',
            gender: '',
            phone: '',
            email: '',
            address: '',
            blood_type: '',
            allergies: '',
            previous_conditions: '',
            insurance_type: '',
            status: 'activo',
        };
    },
};
