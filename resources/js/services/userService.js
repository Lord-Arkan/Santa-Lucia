export const userService = {
    defaultForm(modulePermissions = []) {
        return {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            rol: 'asistente',
            module_permissions: [...modulePermissions],
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
