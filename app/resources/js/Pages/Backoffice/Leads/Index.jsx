import Card from '@/Components/Card';
import EmptyState from '@/Components/EmptyState';
import { InboxIcon } from '@/Components/icons';
import { Head } from '@inertiajs/react';

/**
 * Backoffice — lista de leads B2B (STORY-027 · EPIC-005/ADR-009). Superfície interna do operador:
 * tabela no desktop, lista de cards no mobile. Reusa o padrão visual do Backoffice de saques. Só
 * leitura; PII visível apenas atrás da barreira do papel operacional. pt-BR, tokens do DS.
 */
export default function Index({ leads }) {
    return (
        <div className="min-h-screen bg-canvas-soft" data-testid="backoffice-leads">
            <Head title="Leads · Backoffice" />

            <header className="flex items-center gap-sm border-b border-ink bg-canvas px-xl py-md">
                <span className="font-display font-black text-ink">Quantah</span>
                <span className="text-body-sm text-mute">· Backoffice</span>
            </header>

            <main className="mx-auto flex max-w-4xl flex-col gap-lg px-lg py-2xl">
                <h1 className="text-display-sm text-ink">Leads</h1>

                {leads.length === 0 ? (
                    <EmptyState
                        icon={<InboxIcon className="h-3xl w-3xl" />}
                        title="Nenhum lead capturado ainda."
                        data-testid="backoffice-vazio"
                    />
                ) : (
                    <>
                        {/* Desktop: tabela */}
                        <Card className="hidden overflow-x-auto lg:block">
                            <table className="w-full border-collapse text-left">
                                <caption className="sr-only">Leads B2B capturados</caption>
                                <thead>
                                    <tr className="border-b border-ink/10 text-body-sm text-body">
                                        <th scope="col" className="py-xs pr-md font-semibold">Nome</th>
                                        <th scope="col" className="py-xs pr-md font-semibold">E-mail</th>
                                        <th scope="col" className="py-xs pr-md font-semibold">Empresa</th>
                                        <th scope="col" className="py-xs font-semibold">Captado em</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {leads.map((lead) => (
                                        <tr
                                            key={lead.id}
                                            className="border-b border-ink/10 last:border-0"
                                            data-testid="backoffice-lead-row"
                                        >
                                            <td className="py-md pr-md text-body-md text-ink">{lead.nome}</td>
                                            <td className="py-md pr-md text-body-md text-body">{lead.email}</td>
                                            <td className="py-md pr-md text-body-md text-body">{lead.empresa}</td>
                                            <td className="py-md text-body-sm text-mute">{lead.captado_em}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </Card>

                        {/* Mobile: lista de cards */}
                        <ul className="flex flex-col gap-md lg:hidden">
                            {leads.map((lead) => (
                                <li key={lead.id}>
                                    <Card className="flex flex-col gap-xs" data-testid="backoffice-lead-row">
                                        <span className="text-body-md font-semibold text-ink">{lead.nome}</span>
                                        <span className="text-body-sm text-body">{lead.email}</span>
                                        <span className="text-body-sm text-body">{lead.empresa}</span>
                                        <span className="text-body-sm text-mute">{lead.captado_em}</span>
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
