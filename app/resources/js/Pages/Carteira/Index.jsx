import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import { ReceiptIcon, WalletIcon } from '@/Components/icons';
import AppLayout from '@/Layouts/AppLayout';
import Snackbar from '@/Components/Snackbar';
import { Head, Link, router, usePage } from '@inertiajs/react';

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

// Item do histórico: cupom válido + o crédito. Clicável → abre o detalhe (STORY-034).
// Mostra o estabelecimento e a data de emissão (contexto), além do valor/crédito.
function ItemExtrato({ item }) {
    return (
        <li>
            <Link
                href={`/carteira/cupom/${item.cupom_id}`}
                data-testid="screen-carteira-item"
                className="block rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ink"
            >
                <Card variant="content" className="flex items-center gap-md">
                    <ReceiptIcon className="h-xl w-xl shrink-0 text-ink-deep" />
                    <div className="flex min-w-0 flex-1 flex-col">
                        <span className="truncate text-body-md font-semibold text-ink">
                            {item.estabelecimento}
                        </span>
                        <span className="text-body-sm text-mute">
                            {item.data ? `${item.data} · ` : ''}R$ {item.cupom_valor}
                        </span>
                    </div>
                    <Badge variant="positive" data-testid="screen-carteira-item-credito">
                        +R$ {item.credito}
                    </Badge>
                </Card>
            </Link>
        </li>
    );
}

/**
 * Carteira do Colaborador (STORY-016). Saldo em reais (card de marca) + histórico de
 * cupons válidos e créditos de cashback. Só leitura; dados vêm das props do servidor
 * (read-model ExtratoCarteira, STORY-015) — nada hardcoded. Saque é a STORY-017.
 *
 * Navegação e retorno vêm da casca `AppLayout` (pattern.app-shell, DDR-007) — seção "Carteira" ativa.
 * Estados: **preenchido** (saldo>0 + histórico) e **vazio** (saldo R$0,00 + CTA).
 */
export default function Index({ saldo, extrato }) {
    const temExtrato = extrato.length > 0;
    const podeSacar = saldo.centavos > 0;
    const saqueFlash = usePage().props.flash?.saque ?? null;

    return (
        <AppLayout active="carteira">
            <Head title="Carteira" />

            <div
                data-testid="screen-carteira"
                className="mx-auto flex w-full max-w-2xl flex-col gap-xl px-lg py-2xl"
            >
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
                    <span className="text-body-sm text-primary-neutral">{COPY.saldoLabel}</span>
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
        </AppLayout>
    );
}
