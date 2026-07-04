import Button from '@/Components/Button';
import AuthDivider from '@/Components/brand/AuthDivider';
import GoogleButton from '@/Components/brand/GoogleButton';
import TextField from '@/Components/inputs/TextField';
import { t } from '@/i18n';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const LINK =
    'font-semibold text-ink underline underline-offset-2 hover:text-ink-deep rounded ' +
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title={t('Create account')} />

            <div className="flex flex-col gap-lg">
                <header>
                    <h1 className="text-display-xs font-black text-ink">
                        {t('Create account')}
                    </h1>
                    <p className="mt-xxs text-body-sm text-body">
                        {t(
                            'It takes less than a minute. Every receipt becomes money in your wallet.',
                        )}
                    </p>
                </header>

                <GoogleButton label={t('Sign up with Google')} />
                <AuthDivider>{t('or')}</AuthDivider>

                <form onSubmit={submit} className="flex flex-col gap-lg">
                    <TextField
                        label={t('Name')}
                        autoComplete="name"
                        required
                        data-testid="acesso-campo-nome"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        error={errors.name}
                    />
                    <TextField
                        label={t('Email')}
                        type="email"
                        autoComplete="username"
                        required
                        data-testid="acesso-campo-email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        error={errors.email}
                    />
                    <TextField
                        label={t('Password')}
                        type="password"
                        autoComplete="new-password"
                        required
                        hint={t('Use at least 8 characters.')}
                        data-testid="acesso-campo-senha"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        error={errors.password}
                    />
                    <TextField
                        label={t('Confirm password')}
                        type="password"
                        autoComplete="new-password"
                        required
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
                        data-testid="acesso-criar-submit"
                        className="w-full"
                    >
                        {t('Create account')}
                    </Button>
                </form>

                <p className="text-center text-body-sm text-body">
                    {t('Already have an account?')}{' '}
                    <Link
                        href={route('login')}
                        className={LINK}
                        data-testid="acesso-ir-entrar"
                    >
                        {t('Sign in')}
                    </Link>
                </p>
            </div>
        </GuestLayout>
    );
}
