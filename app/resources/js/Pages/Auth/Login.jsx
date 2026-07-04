import Button from '@/Components/Button';
import AuthCallout from '@/Components/brand/AuthCallout';
import AuthDivider from '@/Components/brand/AuthDivider';
import GoogleButton from '@/Components/brand/GoogleButton';
import Checkbox from '@/Components/inputs/Checkbox';
import TextField from '@/Components/inputs/TextField';
import { t } from '@/i18n';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const LINK =
    'font-semibold text-ink underline underline-offset-2 hover:text-ink-deep rounded ' +
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    // Erro de login com Google (STORY-022) chega flashado nas props da página (não no useForm,
    // que só popula no submit deste form).
    const pageErrors = usePage().props.errors;

    // CA-3: erro de autenticação vira callout GLOBAL — credencial inválida (sem vazar qual campo
    // falhou) ou falha no login com Google.
    const credentialError = errors.email || errors.password || pageErrors.google;

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), { onFinish: () => reset('password') });
    };

    return (
        <GuestLayout>
            <Head title={t('Sign in')} />

            <div className="flex flex-col gap-lg">
                <header>
                    <h1 className="text-display-xs font-black text-ink">{t('Sign in')}</h1>
                    <p className="mt-xxs text-body-sm text-body">
                        {t('Good to see you again.')}
                    </p>
                </header>

                <GoogleButton label={t('Sign in with Google')} />
                <AuthDivider>{t('or')}</AuthDivider>

                {status && <AuthCallout variant="ok">{status}</AuthCallout>}
                {credentialError && (
                    <AuthCallout variant="error" data-testid="acesso-erro-credencial">
                        {credentialError}
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
                    />
                    <TextField
                        label={t('Password')}
                        type="password"
                        autoComplete="current-password"
                        data-testid="acesso-campo-senha"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <div className="flex flex-wrap items-center justify-between gap-md">
                        <Checkbox
                            label={t('Keep me signed in')}
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        {canResetPassword && (
                            <Link
                                href={route('password.request')}
                                className={LINK}
                                data-testid="acesso-esqueci-link"
                            >
                                {t('I forgot my password')}
                            </Link>
                        )}
                    </div>

                    <Button
                        type="submit"
                        variant="primary"
                        loading={processing}
                        data-testid="acesso-entrar-submit"
                        className="w-full"
                    >
                        {t('Sign in')}
                    </Button>
                </form>

                <p className="text-center text-body-sm text-body">
                    {t("Don't have an account yet?")}{' '}
                    <Link
                        href={route('register')}
                        className={LINK}
                        data-testid="acesso-ir-criar-conta"
                    >
                        {t('Create account')}
                    </Link>
                </p>
            </div>
        </GuestLayout>
    );
}
