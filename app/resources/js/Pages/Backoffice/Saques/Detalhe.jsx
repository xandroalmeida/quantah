import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import DangerButton from '@/Components/DangerButton';
import Snackbar from '@/Components/Snackbar';
import TextField from '@/Components/inputs/TextField';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

const STATUS = {
    solicitado: { label: 'solicitado', variant: 'info' },
    em_analise: { label: 'em análise', variant: 'warning' },
    aprovado: { label: 'aprovado', variant: 'positive' },
    pago: { label: 'pago', variant: 'positive' },
    rejeitado: { label: 'rejeitado', variant: 'negative' },
};

function Linha({ k, children }) {
    return (
        <div className="flex justify-between border-b border-ink/10 pb-sm text-body-md">
            <span className="text-body">{k}</span>
            <span className="font-semibold text-ink">{children}</span>
        </div>
    );
}

/**
 * Backoffice — detalhe do saque + ações da máquina de estados (STORY-017, ADR-005).
 * O operador confere CPF × chave PIX (titularidade) e conduz o fluxo. Rejeitar estorna.
 */
export default function Detalhe({ saque }) {
    const s = STATUS[saque.status] ?? STATUS.solicitado;
    const { errors } = usePage().props;
    const pagar = useForm({ comprovante: '' });

    const post = (acao) => router.post(`/backoffice/saques/${saque.id}/${acao}`);
    const rejeitar = () => {
        if (window.confirm('Rejeitar este saque? O valor volta para o saldo do Colaborador.')) {
            router.post(`/backoffice/saques/${saque.id}/rejeitar`);
        }
    };
    const submitPagar = (e) => {
        e.preventDefault();
        pagar.post(`/backoffice/saques/${saque.id}/pagar`);
    };

    return (
        <div className="min-h-screen bg-canvas-soft" data-testid="backoffice-saque-detalhe">
            <Head title="Saque · Backoffice" />
            <header className="flex items-center gap-sm border-b border-ink bg-canvas px-xl py-md">
                <span className="font-display font-black text-ink">Quantah</span>
                <span className="text-body-sm text-mute">· Backoffice</span>
            </header>

            <main className="mx-auto flex max-w-lg flex-col gap-lg px-lg py-2xl">
                <Link href="/backoffice/saques" className="text-body-sm font-semibold text-body">← Voltar</Link>
                <h1 className="flex items-center gap-md text-display-sm text-ink">
                    Saque <Badge variant={s.variant} data-testid="backoffice-saque-status">{s.label}</Badge>
                </h1>

                {errors.saque && (
                    <Snackbar variant="danger" data-testid="backoffice-erro">{errors.saque}</Snackbar>
                )}

                <Card className="flex flex-col gap-md">
                    <Linha k="Valor">R$ {saque.valor_reais}</Linha>
                    <Linha k="CPF (chave PIX)">{saque.cpf}</Linha>
                    <Linha k="Solicitado">{saque.solicitado_em}</Linha>
                    {saque.comprovante && <Linha k="Comprovante">{saque.comprovante}</Linha>}
                </Card>

                {saque.status === 'solicitado' && (
                    <Button variant="primary" onClick={() => post('assumir')} data-testid="backoffice-acao-assumir">
                        Assumir
                    </Button>
                )}

                {saque.status === 'em_analise' && (
                    <div className="flex gap-sm">
                        <Button variant="primary" onClick={() => post('aprovar')} data-testid="backoffice-acao-aprovar">
                            Aprovar
                        </Button>
                        <DangerButton onClick={rejeitar} data-testid="backoffice-acao-rejeitar">
                            Rejeitar
                        </DangerButton>
                    </div>
                )}

                {saque.status === 'aprovado' && (
                    <form onSubmit={submitPagar} className="flex flex-col gap-md">
                        <TextField
                            label="Comprovante (e2e / id do PIX)"
                            value={pagar.data.comprovante}
                            onChange={(e) => pagar.setData('comprovante', e.target.value)}
                            error={pagar.errors.comprovante}
                            errorTestId="backoffice-erro-comprovante"
                            data-testid="backoffice-comprovante"
                        />
                        <Button type="submit" variant="primary" loading={pagar.processing} data-testid="backoffice-acao-pagar">
                            Marcar pago
                        </Button>
                    </form>
                )}
            </main>
        </div>
    );
}
