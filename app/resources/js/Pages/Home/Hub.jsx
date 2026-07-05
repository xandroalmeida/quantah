import Button from '@/Components/Button';
import BrandMark from '@/Components/brand/BrandMark';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import { PlusIcon, ReceiptIcon, WalletIcon } from '@/Components/icons';
import AppLayout from '@/Layouts/AppLayout';
import { t } from '@/i18n';
import { Head, Link, router, usePage } from '@inertiajs/react';

// Textos via o mecanismo de i18n (STORY-020 · IDR-010): chave = string-fonte em inglês,
// resolvida para pt-BR em lang/pt_BR.json. Microcopy = screen-spec §5 (STORY-029 e STORY-030).

/** Primeiro nome do Coletador para a saudação (do `auth.user` compartilhado). */
function primeiroNome(user) {
    const nome = (user?.name ?? '').trim();

    return nome === '' ? '' : nome.split(/\s+/)[0];
}

// Atalho rápido da home (card.content clicável) — leva a um sub-destino da jornada em 1 toque.
function Atalho({ href, icon, label, testid }) {
    return (
        <Link
            href={href}
            data-testid={testid}
            className="flex min-h-3xl flex-col gap-sm rounded-xl bg-canvas p-lg text-ink transition-colors duration-fast hover:bg-primary-pale focus:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2 focus-visible:ring-offset-canvas-soft"
        >
            <span aria-hidden="true" className="text-ink-deep">
                {icon}
            </span>
            <span className="text-body-md font-semibold">{label}</span>
        </Link>
    );
}

/**
 * Home-hub do Coletador (STORY-029/030 · EPIC-006) — destino pós-login da área B2C.
 *
 * Numa olhada: **o que ganhei** (saldo, card de marca) e **o que faço agora** (CTA verde
 * "Coletar cupom"). Abaixo, os **atalhos rápidos** (Histórico → extrato, Prêmios → saque) — 1 toque
 * cada (STORY-030). A navegação e o retorno vêm da casca `AppLayout` (pattern.app-shell, DDR-007).
 * Dados vêm do servidor (read-model ExtratoCarteira) — nada hardcoded. Estados: **positivo** (saldo>0)
 * e **zero/primeiro acesso** (saldo R$0,00 + bloco acolhedor).
 */
export default function Hub({ saldo }) {
    const user = usePage().props.auth?.user ?? null;
    const nome = primeiroNome(user);
    const saudacao = nome === '' ? t('Hi') : t('Hi, :name', { name: nome });
    const temSaldo = saldo.centavos > 0;

    return (
        <AppLayout active="inicio">
            <Head title={t('Home')} />

            <div className="mx-auto flex w-full max-w-2xl flex-col gap-xl px-lg py-2xl">
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
                    <span className="text-body-sm text-primary-neutral">{t('Your balance')}</span>
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

                {/* Atalhos rápidos (STORY-030) — 1 toque para extrato/saque. Sempre visíveis. */}
                <div className="grid grid-cols-2 gap-md">
                    <Atalho
                        href="/carteira"
                        icon={<ReceiptIcon className="h-xl w-xl" />}
                        label={t('History')}
                        testid="screen-home-atalho-historico"
                    />
                    <Atalho
                        href="/carteira/saque"
                        icon={<WalletIcon className="h-xl w-xl" />}
                        label={t('Rewards')}
                        testid="screen-home-atalho-premios"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
