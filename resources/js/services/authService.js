import { router } from '@inertiajs/vue3';

export const authService = {
    login(form, options = {}) {
        form.transform((data) => ({
            ...data,
            remember: data.remember ? 'on' : '',
        })).post(route('login'), options);
    },

    logout(options = {}) {
        router.post(route('logout'), {}, options);
    },
};
