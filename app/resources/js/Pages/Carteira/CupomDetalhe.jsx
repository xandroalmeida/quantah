import Badge from '@/Components/Badge';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import { ReceiptIcon } from '@/Components/icons';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';

// Microcopy da tela (pt-BR). Conteúdo vem 100% do read-model DetalheCupom (nada hardcoded de dado).
const COPY = {
    voltar: 'Carteira',
    total: 'Total',
    itens: 'Itens',
    vazioTitulo: 'Estamos processando este cupom',
    vazioInstrucao: 'Os itens aparecem assim que a validação na SEFAZ terminar.',
};

/**
 * Detalhe do cupom (STORY-034 · SCREEN-STORY-034). Responde "que compra foi essa e o que tinha
 * nela": cabeçalho (estabelecimento com fallback, CNPJ, data, total, status) + lista de itens.
 * Mobile-first sobre o DS; retorno à listagem pela casca (AppLayout, DDR-007) e pelo link "Carteira".
 * Só leitura; dados do read-model (sem PII — ADR-006).
 */
export default function CupomDetalhe({ cupom }) {
    const temItens = cupom.itens.length > 0;

    return (
        <AppLayout active="carteira">
            <Head title="Cupom" />

            <div
                data-testid="screen-cupom-detalhe"
                className="mx-auto flex w-full max-w-2xl flex-col gap-xl px-lg py-2xl"
            >
                <Link
                    href="/carteira"
                    data-testid="screen-cupom-voltar"
                    className="inline-flex min-h-3xl items-center gap-xs self-start rounded-sm text-body-md font-semibold text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-ink"
                >
                    <span aria-hidden="true">&larr;</span> {COPY.voltar}
                </Link>

                <Card
                    variant="content"
                    className="flex flex-col gap-md"
                    data-testid="screen-cupom-cabecalho"
                >
                    <div className="flex items-start justify-between gap-md">
                        <div className="flex min-w-0 flex-col gap-xxs">
                            <h1 className="break-words text-display-xs text-ink">
                                {cupom.estabelecimento}
                            </h1>
                            <span className="text-body-sm text-mute">CNPJ {cupom.cnpj}</span>
                            {cupom.data && (
                                <span className="text-body-sm text-mute" data-testid="screen-cupom-data">
                                    {cupom.data}
                                </span>
                            )}
                        </div>
                        <Badge
                            variant={cupom.status.variante}
                            className="shrink-0"
                            data-testid="screen-cupom-status"
                        >
                            {cupom.status.label}
                        </Badge>
                    </div>

                    {cupom.valor_total && (
                        <div className="flex items-baseline justify-between border-t border-canvas-soft pt-md">
                            <span className="text-body-sm text-body">{COPY.total}</span>
                            <span
                                className="font-display text-display-sm font-black text-ink"
                                data-testid="screen-cupom-total"
                            >
                                R$ {cupom.valor_total}
                            </span>
                        </div>
                    )}
                </Card>

                {temItens ? (
                    <section data-testid="screen-cupom-itens">
                        <h2 className="mb-md text-body-md font-semibold text-ink">{COPY.itens}</h2>
                        <ul className="flex flex-col gap-md">
                            {cupom.itens.map((item, i) => (
                                <li key={i}>
                                    <Card
                                        variant="content"
                                        className="flex flex-col gap-xs"
                                        data-testid="screen-cupom-item"
                                    >
                                        <span className="break-words text-body-md font-semibold text-ink">
                                            {item.descricao}
                                        </span>
                                        <div className="flex items-baseline justify-between gap-md text-body-sm text-body">
                                            <span>
                                                {item.quantidade} {item.unidade} &times; R$ {item.valor_unitario}
                                            </span>
                                            <span className="shrink-0 font-semibold text-ink">
                                                R$ {item.valor_total}
                                            </span>
                                        </div>
                                    </Card>
                                </li>
                            ))}
                        </ul>
                    </section>
                ) : (
                    <EmptyState
                        icon={<ReceiptIcon className="h-3xl w-3xl" />}
                        title={COPY.vazioTitulo}
                        data-testid="screen-cupom-vazio"
                    >
                        {COPY.vazioInstrucao}
                    </EmptyState>
                )}
            </div>
        </AppLayout>
    );
}
