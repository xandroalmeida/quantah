import Badge from '@/Components/Badge';
import Card from '@/Components/Card';
import { Head, Link } from '@inertiajs/react';

function Linha({ k, children }) {
    return (
        <div className="flex justify-between gap-md border-b border-ink/10 pb-sm text-body-md">
            <span className="text-body">{k}</span>
            <span className="text-right font-semibold text-ink">{children}</span>
        </div>
    );
}

/**
 * Backoffice — detalhe do cupom + emitente enriquecido (STORY-041 · EPIC-009).
 * Mostra razão social, CNAE, situação cadastral e município/UF. Quando o
 * enriquecimento está pendente/indisponível, o estado aparece explícito — nunca campo
 * vazio mudo (CA-4).
 */
export default function Detalhe({ cupom, emitente }) {
    const temDados = emitente.razao_social !== null || emitente.cnae !== null;

    return (
        <div className="min-h-screen bg-canvas-soft" data-testid="backoffice-cupom-detalhe">
            <Head title="Cupom · Backoffice" />
            <header className="flex items-center gap-sm border-b border-ink bg-canvas px-xl py-md">
                <span className="font-display font-black text-ink">Quantah</span>
                <span className="text-body-sm text-mute">· Backoffice</span>
            </header>

            <main className="mx-auto flex max-w-lg flex-col gap-lg px-lg py-2xl">
                <Link href="/backoffice/cupons" className="text-body-sm font-semibold text-body">← Voltar</Link>

                <section className="flex flex-col gap-md">
                    <h1 className="text-display-sm text-ink">Emitente</h1>
                    <Badge variant={emitente.badge_variante} data-testid="emitente-estado">
                        {emitente.estado_rotulo}
                    </Badge>

                    <Card className="flex flex-col gap-md">
                        <Linha k="CNPJ">{emitente.cnpj}</Linha>
                        {temDados ? (
                            <>
                                <Linha k="Razão social">
                                    <span data-testid="emitente-razao">{emitente.razao_social ?? '—'}</span>
                                </Linha>
                                {emitente.nome_fantasia && <Linha k="Nome fantasia">{emitente.nome_fantasia}</Linha>}
                                <Linha k="CNAE">
                                    <span data-testid="emitente-cnae">{emitente.cnae ?? 'Não informado'}</span>
                                </Linha>
                                <Linha k="Situação cadastral">{emitente.situacao_cadastral ?? '—'}</Linha>
                                <Linha k="Município/UF">{emitente.localizacao ?? '—'}</Linha>
                            </>
                        ) : (
                            <p className="text-body-md text-body" data-testid="emitente-sem-dados">
                                {emitente.estado === 'pendente'
                                    ? 'O enriquecimento deste CNPJ ainda está na fila. Recarregue em instantes.'
                                    : 'Não foi possível obter os dados cadastrais deste CNPJ na Receita.'}
                            </p>
                        )}
                    </Card>
                </section>

                <section className="flex flex-col gap-md">
                    <h2 className="text-body-lg font-semibold text-ink">Cupom</h2>
                    <Card className="flex flex-col gap-md">
                        <Linha k="Estabelecimento (nota)">{cupom.nome_emitente ?? 'Estabelecimento não identificado'}</Linha>
                        {cupom.emissao && <Linha k="Emissão">{cupom.emissao}</Linha>}
                        {cupom.valor_total && <Linha k="Valor total">{cupom.valor_total}</Linha>}
                        <Linha k="Itens">{cupom.itens}</Linha>
                        <Linha k="Chave de acesso">
                            <span className="break-all font-mono text-body-sm">{cupom.chave_acesso}</span>
                        </Linha>
                    </Card>
                </section>
            </main>
        </div>
    );
}
