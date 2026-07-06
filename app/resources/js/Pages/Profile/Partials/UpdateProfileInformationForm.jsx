import Button from '@/Components/Button';
import TextField from '@/Components/inputs/TextField';
import { t } from '@/i18n';
import { Transition } from '@headlessui/react';
import { Link, useForm, usePage } from '@inertiajs/react';

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    className = '',
}) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            email: user.email,
        });

    const submit = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-body-lg font-semibold text-ink">
                    {t('Profile Information')}
                </h2>

                <p className="mt-xs text-body-sm text-body">
                    {t("Update your account's profile information.")}
                </p>
            </header>

            <form onSubmit={submit} className="mt-xl flex flex-col gap-lg">
                <TextField
                    id="name"
                    label={t('Name')}
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    required
                    autoComplete="name"
                    error={errors.name}
                />

                <TextField
                    id="email"
                    type="email"
                    label={t('Email')}
                    value={data.email}
                    disabled
                    autoComplete="username"
                    hint={t('Your email address cannot be changed.')}
                />

                {mustVerifyEmail && user.email_verified_at === null && (
                    <div>
                        <p className="text-body-sm text-body">
                            {t('Your email address is unverified.')}
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="rounded-md text-body-sm text-ink-deep underline hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2"
                            >
                                {t(
                                    'Click here to re-send the verification email.',
                                )}
                            </Link>
                        </p>

                        {status === 'verification-link-sent' && (
                            <div className="mt-sm text-body-sm font-semibold text-ink-deep">
                                {t(
                                    'A new verification link has been sent to your email address.',
                                )}
                            </div>
                        )}
                    </div>
                )}

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
