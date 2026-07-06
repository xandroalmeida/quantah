import Badge from '@/Components/Badge';
import Card from '@/Components/Card';
import { Head, Link } from '@inertiajs/react';

/**
 * Backoffice — cupons processados com o estado do enriquecimento do emitente
 * (STORY-041 · EPIC-009). O operador vê a categoria de cada estabelecimento; o detalhe
 * abre os dados cadastrais completos.
 */
export default function Index({ cupons }) {
    return (
        <div className="min-h-screen bg-canvas-soft" data-testid="backoffice-cupons">
            <Head title="Cupons · Backoffice" />
            <header className="flex items-center gap-sm border-b border-ink bg-canvas px-xl py-md">
                <span className="font-display font-black text-ink">Quantah</span>
                <span className="text-body-sm text-mute">· Backoffice</span>
            </header>

            <main className="mx-auto flex max-w-2xl flex-col gap-lg px-lg py-2xl">
                <h1 className="text-display-sm text-ink">Cupons processados</h1>

                {cupons.data.length === 0 && (
                    <Card className="text-body-md text-body" data-testid="backoffice-cupons-vazio">
                        Nenhum cupom processado ainda.
                    </Card>
                )}

                <ul className="flex flex-col gap-md">
                    {cupons.data.map((cupom) => (
                        <li key={cupom.id} data-testid="backoffice-cupom-item">
                            <Link href={`/backoffice/cupons/${cupom.id}`} className="block">
                                <Card className="flex flex-col gap-sm transition hover:border-ink/30">
                                    <div className="flex items-center justify-between gap-md">
                                        <span className="font-semibold text-ink">
                                            {cupom.estabelecimento ?? 'Estabelecimento não identificado'}
                                        </span>
                                        <Badge variant={cupom.enriquecimento.badge_variante}>
                                            {cupom.enriquecimento.estado_rotulo}
                                        </Badge>
                                    </div>
                                    <div className="flex justify-between text-body-sm text-body">
                                        <span>{cupom.cnpj}</span>
                                        <span>{cupom.emissao}</span>
                                    </div>
                                    <span className="font-mono text-body-sm text-mute">{cupom.chave_acesso}</span>
                                </Card>
                            </Link>
                        </li>
                    ))}
                </ul>
            </main>
        </div>
    );
}
