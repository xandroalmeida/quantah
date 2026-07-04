import Button from '@/Components/Button';
import AuthCallout from '@/Components/brand/AuthCallout';
import { InboxIcon } from '@/Components/icons';
import { t } from '@/i18n';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const LINK =
    'font-semibold text-ink underline underline-offset-2 hover:text-ink-deep rounded ' +
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title={t('Confirm your email')} />

            <div className="flex flex-col gap-lg">
                <div className="flex flex-col items-center gap-md text-center">
                    <span className="inline-flex h-3xl w-3xl items-center justify-center rounded-pill bg-primary-pale text-positive-deep">
                        <InboxIcon className="h-xl w-xl" />
                    </span>
                    <header>
                        <h1 className="text-display-xs font-black text-ink">
                            {t('Confirm your email')}
                        </h1>
                        <p className="mt-xxs text-body-sm text-body">
                            {t(
                                'We sent a confirmation link to your email. Open it to activate your account and start sending receipts.',
                            )}
                        </p>
                    </header>
                </div>

                {status === 'verification-link-sent' && (
                    <AuthCallout variant="ok" data-testid="acesso-verif-reenviado">
                        {t('We sent a new confirmation link to your email.')}
                    </AuthCallout>
                )}

                <form onSubmit={submit}>
                    <Button
                        type="submit"
                        variant="primary"
                        loading={processing}
                        data-testid="acesso-verif-reenviar"
                        className="w-full"
                    >
                        {t('Resend email')}
                    </Button>
                </form>

                <p className="text-center text-body-sm text-body">
                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className={LINK}
                        data-testid="acesso-sair"
                    >
                        {t('Log Out')}
                    </Link>
                </p>
            </div>
        </GuestLayout>
    );
}
