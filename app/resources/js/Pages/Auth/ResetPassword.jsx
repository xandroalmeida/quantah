import Button from '@/Components/Button';
import TextField from '@/Components/inputs/TextField';
import { t } from '@/i18n';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title={t('Create new password')} />

            <div className="flex flex-col gap-lg">
                <header>
                    <h1 className="text-display-xs font-black text-ink">
                        {t('Create new password')}
                    </h1>
                    <p className="mt-xxs text-body-sm text-body">
                        {t('Choose a new password for your account.')}
                    </p>
                </header>

                <form onSubmit={submit} className="flex flex-col gap-lg">
                    <TextField
                        label={t('Email')}
                        type="email"
                        autoComplete="username"
                        readOnly
                        data-testid="acesso-campo-email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        error={errors.email}
                    />
                    <TextField
                        label={t('New password')}
                        type="password"
                        autoComplete="new-password"
                        hint={t('Use at least 8 characters.')}
                        data-testid="acesso-campo-senha"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        error={errors.password}
                    />
                    <TextField
                        label={t('Confirm new password')}
                        type="password"
                        autoComplete="new-password"
                        data-testid="acesso-campo-senha_conf"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        error={errors.password_confirmation}
                    />

                    <Button
                        type="submit"
                        variant="primary"
                        loading={processing}
                        data-testid="acesso-nova-senha-submit"
                        className="w-full"
                    >
                        {t('Reset password')}
                    </Button>
                </form>
            </div>
        </GuestLayout>
    );
}
