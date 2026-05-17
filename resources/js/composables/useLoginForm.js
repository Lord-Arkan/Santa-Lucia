import { useForm } from '@inertiajs/vue3';
import { authService } from '@/services/authService';

export function useLoginForm() {
    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = () => {
        authService.login(form, {
            onFinish: () => form.reset('password'),
        });
    };

    return {
        form,
        submit,
    };
}
