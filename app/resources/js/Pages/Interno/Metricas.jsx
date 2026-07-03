import Badge from '@/Components/Badge';
import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import { ReceiptIcon } from '@/Components/icons';
import { Head } from '@inertiajs/react';

// Taxa (0..1) → "72%"; nula (sem envios) → "—".
function formatarTaxa(taxa) {
    return taxa === null || taxa === undefined ? '—' : `${Math.round(taxa * 100)}%`;
}

// Cartão de número do topo (KPI) — rótulo curto + valor grande, superfície do DS.
function StatCard({ label, value, hint, testid }) {
    return (
        <Card className="flex flex-col gap-xxs" data-testid={testid}>
            <span className="text-body-sm text-body">{label}</span>
            <span className="text-display-md text-ink">{value}</span>
            {hint && <span className="text-body-sm text-mute">{hint}</span>}
        </Card>
    );
}

/**
 * Painel interno da north-star (STORY-012 · CA-3). Mostra a métrica que define o MVP —
 * cupons válidos, únicos e novos por semana de coleta — e a taxa de sucesso de envio
 * (enviados → válidos). Só leitura; compõe a superfície do DS (Card/Badge/EmptyState).
 */
export default function Metricas({ resumo, porSemana }) {
    const temDados = porSemana.length > 0;

    return (
        <>
            <Head title="Métricas de coleta" />

            <main className="min-h-screen bg-canvas-soft px-lg py-2xl">
                <div className="mx-auto flex max-w-4xl flex-col gap-xl">
                    <header className="flex flex-col gap-xxs">
                        <h1
                            className="text-display-lg text-ink"
                            data-testid="painel-metricas-title"
                        >
                            Métricas de coleta
                        </h1>
                        <p className="text-body-md text-body">
                            Cupons NFC-e válidos, únicos e novos por semana — a north-star do
                            MVP em São Paulo — e a taxa de sucesso de envio.
                        </p>
                    </header>

                    <section
                        className="grid grid-cols-1 gap-lg sm:grid-cols-3"
                        aria-label="Resumo geral"
                    >
                        <StatCard
                            label="Válidos, únicos e novos"
                            value={resumo.validos_total}
                            hint="acumulado"
                            testid="stat-validos-total"
                        />
                        <StatCard
                            label="Cupons enviados"
                            value={resumo.enviados_total}
                            hint="todas as tentativas"
                            testid="stat-enviados-total"
                        />
                        <StatCard
                            label="Taxa de sucesso"
                            value={formatarTaxa(resumo.taxa_geral)}
                            hint="enviados → válidos"
                            testid="stat-taxa-geral"
                        />
                    </section>

                    {temDados ? (
                        <Card className="overflow-x-auto" data-testid="metricas-tabela">
                            <h2 className="mb-lg text-display-xs text-ink">Por semana</h2>
                            <table className="w-full border-collapse text-left">
                                <caption className="sr-only">
                                    Cupons válidos, únicos e novos e taxa de sucesso por semana
                                    de coleta
                                </caption>
                                <thead>
                                    <tr className="border-b border-ink/10 text-body-sm text-body">
                                        <th scope="col" className="py-xs pr-md font-semibold">
                                            Semana
                                        </th>
                                        <th scope="col" className="py-xs pr-md font-semibold">
                                            Válidos
                                        </th>
                                        <th scope="col" className="py-xs pr-md font-semibold">
                                            Enviados
                                        </th>
                                        <th scope="col" className="py-xs font-semibold">
                                            Taxa
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {porSemana.map((linha) => (
                                        <tr
                                            key={linha.semana}
                                            className="border-b border-ink/10 last:border-0"
                                            data-testid="semana-row"
                                        >
                                            <th
                                                scope="row"
                                                className="py-md pr-md text-body-md font-normal text-ink"
                                            >
                                                {linha.semana_label}
                                            </th>
                                            <td className="py-md pr-md text-body-md text-ink">
                                                <Badge variant="positive">{linha.validos}</Badge>
                                            </td>
                                            <td className="py-md pr-md text-body-md text-body">
                                                {linha.enviados}
                                            </td>
                                            <td className="py-md text-body-md text-ink">
                                                {formatarTaxa(linha.taxa)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </Card>
                    ) : (
                        <EmptyState
                            icon={<ReceiptIcon className="h-3xl w-3xl" />}
                            title="Ainda sem cupons coletados"
                            data-testid="metricas-vazio"
                        >
                            Assim que o primeiro cupom for validado, a contagem semanal aparece
                            aqui.
                        </EmptyState>
                    )}
                </div>
            </main>
        </>
    );
}
