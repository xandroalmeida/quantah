import Button from '@/Components/Button';
import Card from '@/Components/Card';
import { WalletIcon } from '@/Components/icons';
import AppLayout from '@/Layouts/AppLayout';
import MaskedField from '@/Components/inputs/MaskedField';
import MoneyField from '@/Components/inputs/MoneyField';
import { Head, router, useForm } from '@inertiajs/react';

// Máscara de CPF (chave PIX): 11 dígitos em 000.000.000-00. O valor guardado é só dígitos.
const CPF_MASK = '000.000.000-00';

// Microcopy = screen-spec §5 (STORY-017-solicitar-saque).
const COPY = {
    titulo: 'Sacar',
    saldoLabel: 'Saldo disponível',
    valorLabel: 'Quanto você quer sacar? (R$)',
    valorPlaceholder: '0,00',
    cpfLabel: 'CPF (sua chave PIX)',
    cpfPlaceholder: '000.000.000-00',
    nota: 'Você recebe via PIX na chave do tipo CPF.',
    submit: 'Solicitar saque',
    cancelar: 'Cancelar',
};

/**
 * Solicitar saque (STORY-017, ADR-005) — PIX assistido. Um campo de valor + um campo CPF
 * (que é também a chave PIX, tipo CPF). Validação canônica no servidor (mínimo R$ 5,00,
 * saldo sob lock, CPF válido). Sucesso redireciona à carteira (saldo já reservado).
 */
export default function Solicitar({ saldo }) {
    const { data, setData, post, processing, errors } = useForm({ valor: '', cpf: '' });

    function submit(e) {
        e.preventDefault();
        post('/carteira/saque');
    }

    return (
        <AppLayout active="carteira">
            <Head title="Sacar" />

            <div
                data-testid="screen-saque"
                className="flex min-h-full flex-col items-center px-lg py-2xl"
            >
                <form onSubmit={submit} className="flex w-full max-w-md flex-col gap-lg">
                <h1 className="flex items-center gap-sm text-display-sm text-ink">
                    {COPY.titulo}
                    <WalletIcon className="h-xl w-xl text-mute" />
                </h1>

                <Card variant="feature-dark" className="flex flex-col gap-xxs">
                    <span className="text-body-sm text-primary-neutral">{COPY.saldoLabel}</span>
                    <span
                        className="font-display text-display-sm font-black text-primary"
                        data-testid="screen-saque-saldo"
                    >
                        R$ {saldo.reais}
                    </span>
                </Card>

                <MoneyField
                    label={COPY.valorLabel}
                    placeholder={COPY.valorPlaceholder}
                    value={data.valor}
                    onAccept={(valor) => setData('valor', valor)}
                    error={errors.valor}
                    errorTestId="screen-saque-erro-valor"
                    data-testid="screen-saque-valor"
                    autoFocus
                />

                <MaskedField
                    label={COPY.cpfLabel}
                    mask={CPF_MASK}
                    placeholder={COPY.cpfPlaceholder}
                    value={data.cpf}
                    onAccept={(cpf) => setData('cpf', cpf)}
                    error={errors.cpf}
                    errorTestId="screen-saque-erro-cpf"
                    data-testid="screen-saque-cpf"
                    hint={COPY.nota}
                />

                <Button
                    type="submit"
                    variant="primary"
                    loading={processing}
                    data-testid="screen-saque-submit"
                >
                    {COPY.submit}
                </Button>
                <Button
                    type="button"
                    variant="tertiary"
                    onClick={() => router.visit('/carteira')}
                >
                    {COPY.cancelar}
                </Button>
                </form>
            </div>
        </AppLayout>
    );
}
