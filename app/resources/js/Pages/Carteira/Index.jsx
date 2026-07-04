import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import { HomeIcon, ReceiptIcon, UserIcon, WalletIcon } from '@/Components/icons';
import NavBar from '@/Components/nav/NavBar';
import NavBottom from '@/Components/nav/NavBottom';
import NavLink from '@/Components/nav/NavLink';
import Snackbar from '@/Components/Snackbar';
import { Head, router, usePage } from '@inertiajs/react';

// Microcopy = screen-spec §5 (design/screens/STORY-016-carteira-saldo-historico/screen-spec.md).
const COPY = {
    titulo: 'Carteira',
    saldoLabel: 'Seu saldo',
    saldoHint: 'Cada nota conta.',
    historico: 'Histórico',
    vazioTitulo: 'Seu saldo vai aparecer aqui',
    vazioInstrucao: 'Envie cupons válidos e ganhe 0,1% de cada um em cashback.',
    vazioCta: 'Capturar cupom',
};

// Seções raiz do app do Colaborador (nav.bottom / nav.bar do DS). "Carteira" é a atual.
const SECOES = [
    { label: 'Início', href: '/', icon: <HomeIcon /> },
    { label: 'Cupons', href: '/coletar', icon: <ReceiptIcon /> },
    { label: 'Carteira', href: '/carteira', icon: <WalletIcon />, active: true },
    { label: 'Perfil', href: '/profile', icon: <UserIcon /> },
];

// Item do histórico: cupom válido + o crédito de cashback correspondente.
function ItemExtrato({ item }) {
    return (
        <li>
            <Card
                variant="content"
                className="flex items-center gap-md"
                data-testid="screen-carteira-item"
            >
                <ReceiptIcon className="h-xl w-xl shrink-0 text-ink-deep" />
                <div className="flex min-w-0 flex-1 flex-col">
                    <span className="text-body-md font-semibold text-ink">
                        Cupom de R$ {item.cupom_valor}
                    </span>
                    <span className="text-body-sm text-mute">{item.data}</span>
                </div>
                <Badge variant="positive" data-testid="screen-carteira-item-credito">
                    +R$ {item.credito}
                </Badge>
            </Card>
        </li>
    );
}

/**
 * Carteira do Colaborador (STORY-016). Saldo em reais (card de marca) + histórico de
 * cupons válidos e créditos de cashback. Só leitura; dados vêm das props do servidor
 * (read-model ExtratoCarteira, STORY-015) — nada hardcoded. Saque é a STORY-017.
 *
 * Estados: **preenchido** (saldo>0 + histórico) e **vazio** (saldo R$0,00 + CTA). Como
 * a página é server-rendered, "loading" é a barra de navegação do Inertia e "erro" é a
 * página de erro do framework — não há skeleton/snackbar client-side a materializar aqui.
 */
export default function Index({ saldo, extrato }) {
    const temExtrato = extrato.length > 0;
    const podeSacar = saldo.centavos > 0;
    const saqueFlash = usePage().props.flash?.saque ?? null;

    return (
        <div className="flex h-screen flex-col bg-canvas-soft">
            <Head title="Carteira" />

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
                data-testid="screen-carteira"
                className="flex-1 overflow-y-auto px-lg py-2xl"
            >
                <div className="mx-auto flex w-full max-w-2xl flex-col gap-xl pb-2xl">
                    <h1
                        className="flex items-center justify-between text-display-sm text-ink"
                        data-testid="screen-carteira-title"
                    >
                        {COPY.titulo}
                        <WalletIcon className="h-xl w-xl text-mute" />
                    </h1>

                    <Card
                        variant="feature-dark"
                        className="flex flex-col gap-sm"
                        data-testid="screen-carteira-saldo-card"
                    >
                        <span className="text-body-sm text-primary-neutral">
                            {COPY.saldoLabel}
                        </span>
                        <span
                            className="font-display text-display-lg font-black text-primary"
                            data-testid="screen-carteira-saldo"
                        >
                            R$ {saldo.reais}
                        </span>
                        <span className="text-body-sm text-canvas-soft">{COPY.saldoHint}</span>
                    </Card>

                    {saqueFlash && (
                        <Snackbar variant="success" data-testid="screen-carteira-saque-ok">
                            Saque solicitado. Você recebe no PIX em até 3 dias úteis.
                        </Snackbar>
                    )}

                    {podeSacar && (
                        <Button
                            variant="primary"
                            onClick={() => router.visit('/carteira/saque')}
                            data-testid="screen-carteira-sacar"
                        >
                            Sacar
                        </Button>
                    )}

                    {temExtrato ? (
                        <section data-testid="screen-carteira-historico">
                            <h2 className="mb-md text-body-md font-semibold text-ink">
                                {COPY.historico}
                            </h2>
                            <ul className="flex flex-col gap-md">
                                {extrato.map((item) => (
                                    <ItemExtrato key={item.id} item={item} />
                                ))}
                            </ul>
                        </section>
                    ) : (
                        <EmptyState
                            icon={<ReceiptIcon className="h-3xl w-3xl" />}
                            title={COPY.vazioTitulo}
                            actionLabel={COPY.vazioCta}
                            onAction={() => router.visit('/coletar')}
                            actionProps={{ 'data-testid': 'screen-carteira-vazio-cta' }}
                            data-testid="screen-carteira-vazio"
                        >
                            {COPY.vazioInstrucao}
                        </EmptyState>
                    )}
                </div>
            </main>

            <NavBottom
                className="shrink-0 lg:hidden"
                items={SECOES}
                data-testid="screen-carteira-nav"
            />
        </div>
    );
}
