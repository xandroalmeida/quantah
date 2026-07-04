import Button from '@/Components/Button';
import AuthCallout from '@/Components/brand/AuthCallout';
import TextField from '@/Components/inputs/TextField';
import { t } from '@/i18n';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const LINK =
    'font-semibold text-ink underline underline-offset-2 hover:text-ink-deep rounded ' +
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title={t('Reset password')} />

            <div className="flex flex-col gap-lg">
                <header>
                    <h1 className="text-display-xs font-black text-ink">
                        {t('Reset password')}
                    </h1>
                    <p className="mt-xxs text-body-sm text-body">
                        {t(
                            "Enter your email. We'll send you a link to create a new password.",
                        )}
                    </p>
                </header>

                {status && (
                    <AuthCallout variant="ok" data-testid="acesso-reset-enviado">
                        {status}
                    </AuthCallout>
                )}

                <form onSubmit={submit} className="flex flex-col gap-lg">
                    <TextField
                        label={t('Email')}
                        type="email"
                        autoComplete="username"
                        data-testid="acesso-campo-email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        error={errors.email}
                    />

                    <Button
                        type="submit"
                        variant="primary"
                        loading={processing}
                        data-testid="acesso-reset-submit"
                        className="w-full"
                    >
                        {t('Send link')}
                    </Button>
                </form>

                <p className="text-center text-body-sm text-body">
                    <Link
                        href={route('login')}
                        className={LINK}
                        data-testid="acesso-voltar-entrar"
                    >
                        {t('Back to sign in')}
                    </Link>
                </p>
            </div>
        </GuestLayout>
    );
}
