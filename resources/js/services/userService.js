export const userService = {
    defaultForm() {
        return {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            rol: 'asistente',
            image: null,
        };
    },

    roleLabels: {
        administrador: 'Administrador',
        asistente: 'Asistente',
        doctor: 'Doctor',
        contador: 'Contador',
        jefe_area: 'Jefe de area',
    },

    roleLabel(role) {
        return this.roleLabels[role] ?? role;
    },
};
