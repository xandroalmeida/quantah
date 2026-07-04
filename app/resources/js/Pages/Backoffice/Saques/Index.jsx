import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import { InboxIcon } from '@/Components/icons';
import { Head, Link, router } from '@inertiajs/react';

const STATUS = {
    solicitado: { label: 'solicitado', variant: 'info' },
    em_analise: { label: 'em análise', variant: 'warning' },
    aprovado: { label: 'aprovado', variant: 'positive' },
    pago: { label: 'pago', variant: 'positive' },
    rejeitado: { label: 'rejeitado', variant: 'negative' },
};

const FILTROS = [
    { label: 'Todos', status: null },
    { label: 'Solicitados', status: 'solicitado' },
    { label: 'Em análise', status: 'em_analise' },
    { label: 'Aprovados', status: 'aprovado' },
    { label: 'Pagos', status: 'pago' },
    { label: 'Rejeitados', status: 'rejeitado' },
];

function StatusBadge({ status }) {
    const s = STATUS[status] ?? STATUS.solicitado;
    return (
        <Badge variant={s.variant} data-testid="backoffice-saque-status">
            {s.label}
        </Badge>
    );
}

function Acao({ s }) {
    if (s.status === 'solicitado') {
        return (
            <Button
                variant="secondary"
                onClick={() => router.post(`/backoffice/saques/${s.id}/assumir`)}
                data-testid="backoffice-acao-assumir"
            >
                Assumir
            </Button>
        );
    }
    if (s.status === 'pago' || s.status === 'rejeitado') {
        return <span className="text-body-sm text-mute">—</span>;
    }
    return (
        <Link href={`/backoffice/saques/${s.id}`}>
            <Button variant="secondary">Abrir</Button>
        </Link>
    );
}

/**
 * Backoffice — fila de saques (STORY-017, ADR-005/009). Superfície interna do operador.
 * Tabela no desktop, lista de cards no mobile. CPF mascarado (PII mínima na tela).
 */
export default function Index({ saques, filtro }) {
    return (
        <div className="min-h-screen bg-canvas-soft" data-testid="backoffice-saques">
            <Head title="Saques · Backoffice" />

            <header className="flex items-center gap-sm border-b border-ink bg-canvas px-xl py-md">
                <span className="font-display font-black text-ink">Quantah</span>
                <span className="text-body-sm text-mute">· Backoffice</span>
            </header>

            <main className="mx-auto flex max-w-4xl flex-col gap-lg px-lg py-2xl">
                <h1 className="text-display-sm text-ink">Saques</h1>

                <nav className="flex flex-wrap gap-sm" aria-label="Filtrar por status">
                    {FILTROS.map((f) => (
                        <Link
                            key={f.label}
                            href={f.status ? `/backoffice/saques?status=${f.status}` : '/backoffice/saques'}
                            data-testid={`backoffice-filtro-${f.label.toLowerCase().replace(' ', '-')}`}
                            className={`rounded-pill border px-md py-xs text-body-sm font-semibold ${
                                (filtro ?? null) === f.status
                                    ? 'border-ink bg-ink text-canvas'
                                    : 'border-mute bg-canvas text-body'
                            }`}
                        >
                            {f.label}
                        </Link>
                    ))}
                </nav>

                {saques.length === 0 ? (
                    <EmptyState
                        icon={<InboxIcon className="h-3xl w-3xl" />}
                        title="Nenhum saque neste filtro."
                        data-testid="backoffice-vazio"
                    />
                ) : (
                    <>
                        {/* Desktop: tabela */}
                        <Card className="hidden overflow-x-auto lg:block">
                            <table className="w-full border-collapse text-left">
                                <caption className="sr-only">Saques por status</caption>
                                <thead>
                                    <tr className="border-b border-ink/10 text-body-sm text-body">
                                        <th scope="col" className="py-xs pr-md font-semibold">Valor</th>
                                        <th scope="col" className="py-xs pr-md font-semibold">CPF</th>
                                        <th scope="col" className="py-xs pr-md font-semibold">Status</th>
                                        <th scope="col" className="py-xs pr-md font-semibold">Solicitado</th>
                                        <th scope="col" className="py-xs font-semibold">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {saques.map((s) => (
                                        <tr
                                            key={s.id}
                                            className="border-b border-ink/10 last:border-0"
                                            data-testid="backoffice-saque-row"
                                        >
                                            <td className="py-md pr-md text-body-md text-ink">R$ {s.valor_reais}</td>
                                            <td className="py-md pr-md text-body-md text-body">{s.cpf_mascarado}</td>
                                            <td className="py-md pr-md"><StatusBadge status={s.status} /></td>
                                            <td className="py-md pr-md text-body-sm text-mute">{s.solicitado_em}</td>
                                            <td className="py-md"><Acao s={s} /></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </Card>

                        {/* Mobile: lista de cards */}
                        <ul className="flex flex-col gap-md lg:hidden">
                            {saques.map((s) => (
                                <li key={s.id}>
                                    <Card className="flex flex-col gap-sm" data-testid="backoffice-saque-row">
                                        <div className="flex items-center justify-between">
                                            <span className="text-body-md font-semibold text-ink">R$ {s.valor_reais}</span>
                                            <StatusBadge status={s.status} />
                                        </div>
                                        <span className="text-body-sm text-body">{s.cpf_mascarado} · {s.solicitado_em}</span>
                                        <div><Acao s={s} /></div>
                                    </Card>
                                </li>
                            ))}
                        </ul>
                    </>
                )}
            </main>
        </div>
    );
}
