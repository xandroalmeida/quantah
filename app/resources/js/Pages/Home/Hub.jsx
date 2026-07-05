import Button from '@/Components/Button';
import BrandMark from '@/Components/brand/BrandMark';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import { HomeIcon, PlusIcon, ReceiptIcon, UserIcon, WalletIcon } from '@/Components/icons';
import NavBar from '@/Components/nav/NavBar';
import NavBottom from '@/Components/nav/NavBottom';
import NavLink from '@/Components/nav/NavLink';
import { t } from '@/i18n';
import { Head, router, usePage } from '@inertiajs/react';

// Textos via o mecanismo de i18n (STORY-020 · IDR-010): chave = string-fonte em inglês,
// resolvida para pt-BR em lang/pt_BR.json. Microcopy = screen-spec §5
// (design/screens/STORY-029-home-hub-coletador/screen-spec.md).

// Seções raiz do app do Colaborador (nav.bottom / nav.bar do DS). "Início" é a home-hub.
// Rótulos mantidos como nas telas irmãs (Carteira/Privacidade/Showcase); a navegação coesa
// e a casca compartilhada (extração + i18n dos rótulos) são a STORY-030.
const SECOES = [
    { label: 'Início', href: '/dashboard', icon: <HomeIcon />, active: true },
    { label: 'Cupons', href: '/coletar', icon: <ReceiptIcon /> },
    { label: 'Carteira', href: '/carteira', icon: <WalletIcon /> },
    { label: 'Perfil', href: '/profile', icon: <UserIcon /> },
];

/** Primeiro nome do Coletador para a saudação (do `auth.user` compartilhado). */
function primeiroNome(user) {
    const nome = (user?.name ?? '').trim();

    return nome === '' ? '' : nome.split(/\s+/)[0];
}

/**
 * Home-hub do Coletador (STORY-029 · EPIC-006) — destino pós-login da área B2C.
 *
 * Numa olhada: **o que ganhei** (saldo, card de marca) e **o que faço agora** (CTA verde
 * "Coletar cupom"). Substitui a página genérica de scaffolding. Dados vêm do servidor
 * (read-model ExtratoCarteira) — nada hardcoded. Estados: **positivo** (saldo>0 + CTA) e
 * **zero/primeiro acesso** (saldo R$0,00 + bloco acolhedor com o CTA). Como a página é
 * server-rendered, "loading" é a barra de navegação do Inertia e "erro" é a página de erro
 * do framework — não há skeleton/snackbar client-side a materializar aqui.
 */
export default function Hub({ saldo }) {
    const user = usePage().props.auth?.user ?? null;
    const nome = primeiroNome(user);
    const saudacao = nome === '' ? t('Hi') : t('Hi, :name', { name: nome });
    const temSaldo = saldo.centavos > 0;

    return (
        <div className="flex h-screen flex-col bg-canvas-soft">
            <Head title={t('Home')} />

            <NavBar className="hidden lg:flex">
                <span className="mr-auto font-display text-body-lg font-black text-ink">
                    Quantah
                </span>
                {SECOES.map((s) => (
                    <NavLink key={s.label} href={s.href} active={s.active}>
                        {s.label}
                    </NavLink>
                ))}
            </NavBar>

            <main
                data-testid="screen-home"
                className="flex-1 overflow-y-auto px-lg py-2xl"
            >
                <div className="mx-auto flex w-full max-w-2xl flex-col gap-xl pb-2xl">
                    <div className="flex items-center justify-between gap-md">
                        <h1
                            className="text-display-sm text-ink"
                            data-testid="screen-home-greeting"
                        >
                            {saudacao}
                        </h1>
                        <span data-testid="screen-home-brand">
                            <BrandMark tile="h-2xl w-2xl" />
                        </span>
                    </div>

                    <Card
                        variant="feature-dark"
                        className="flex flex-col gap-sm"
                        data-testid="screen-home-saldo-card"
                    >
                        <span className="text-body-sm text-primary-neutral">
                            {t('Your balance')}
                        </span>
                        <span
                            className="font-display text-display-lg font-black text-primary"
                            data-testid="screen-home-saldo"
                        >
                            R$ {saldo.reais}
                        </span>
                        <span className="text-body-sm text-canvas-soft">
                            {t('Every receipt counts.')}
                        </span>
                    </Card>

                    {temSaldo ? (
                        <>
                            <Button
                                variant="primary"
                                className="w-full"
                                onClick={() => router.visit('/coletar')}
                                data-testid="screen-home-cta"
                            >
                                <PlusIcon className="h-lg w-lg" />
                                {t('Collect coupon')}
                            </Button>
                            <p
                                className="text-center text-body-sm text-body"
                                data-testid="screen-home-support"
                            >
                                {t('Add another receipt and grow your cashback.')}
                            </p>
                        </>
                    ) : (
                        <EmptyState
                            icon={<ReceiptIcon className="h-3xl w-3xl" />}
                            title={t('Start earning cashback')}
                            actionLabel={t('Collect coupon')}
                            onAction={() => router.visit('/coletar')}
                            actionProps={{ 'data-testid': 'screen-home-cta' }}
                            data-testid="screen-home-welcome"
                        >
                            {t('Register a receipt and earn 0.1% of every purchase back.')}
                        </EmptyState>
                    )}
                </div>
            </main>

            <NavBottom
                className="shrink-0 lg:hidden"
                items={SECOES}
                data-testid="screen-home-nav"
            />
        </div>
    );
}
