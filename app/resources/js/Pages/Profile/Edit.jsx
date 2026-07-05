import Card from '@/Components/Card';
import AppLayout from '@/Layouts/AppLayout';
import { t } from '@/i18n';
import { Head } from '@inertiajs/react';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

/**
 * Perfil do Coletador. Usa a casca da área logada (`AppLayout`, DDR-007) — seção "Perfil" ativa,
 * marca Quantah, **sem o logo do Laravel** nem o menu genérico do Breeze (era `AuthenticatedLayout`).
 * Os formulários (Breeze) seguem funcionais; a jornada tem retorno consistente pela barra.
 */
export default function Edit({ mustVerifyEmail, status }) {
    return (
        <AppLayout active="perfil">
            <Head title={t('Profile')} />

            <div
                data-testid="screen-perfil"
                className="mx-auto flex w-full max-w-2xl flex-col gap-lg px-lg py-2xl"
            >
                <h1 className="text-display-sm text-ink">{t('Profile')}</h1>

                <Card variant="content">
                    <UpdateProfileInformationForm
                        mustVerifyEmail={mustVerifyEmail}
                        status={status}
                        className="max-w-xl"
                    />
                </Card>

                <Card variant="content">
                    <UpdatePasswordForm className="max-w-xl" />
                </Card>
            </div>
        </AppLayout>
    );
}
