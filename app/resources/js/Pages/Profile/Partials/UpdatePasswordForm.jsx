import Button from '@/Components/Button';
import TextField from '@/Components/inputs/TextField';
import { t } from '@/i18n';
import { Transition } from '@headlessui/react';
import { useForm } from '@inertiajs/react';

export default function UpdatePasswordForm({ className = '' }) {
    const {
        data,
        setData,
        errors,
        put,
        reset,
        processing,
        recentlySuccessful,
    } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset('password', 'password_confirmation');
                }

                if (errors.current_password) {
                    reset('current_password');
                }
            },
        });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-body-lg font-semibold text-ink">
                    {t('Update Password')}
                </h2>

                <p className="mt-xs text-body-sm text-body">
                    {t(
                        'Ensure your account is using a long, random password to stay secure.',
                    )}
                </p>
            </header>

            <form onSubmit={updatePassword} className="mt-xl flex flex-col gap-lg">
                <TextField
                    id="current_password"
                    type="password"
                    label={t('Current Password')}
                    value={data.current_password}
                    onChange={(e) => setData('current_password', e.target.value)}
                    autoComplete="current-password"
                    error={errors.current_password}
                />

                <TextField
                    id="password"
                    type="password"
                    label={t('New Password')}
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    autoComplete="new-password"
                    error={errors.password}
                />

                <TextField
                    id="password_confirmation"
                    type="password"
                    label={t('Confirm Password')}
                    value={data.password_confirmation}
                    onChange={(e) =>
                        setData('password_confirmation', e.target.value)
                    }
                    autoComplete="new-password"
                    error={errors.password_confirmation}
                />

                <div className="flex items-center gap-lg">
                    <Button type="submit" variant="primary" loading={processing}>
                        {t('Save')}
                    </Button>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-body-sm text-body">{t('Saved.')}</p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
