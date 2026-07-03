import Badge from '@/Components/Badge';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import QrScanner from '@/Components/coleta/QrScanner';
import { QrIcon, ReceiptIcon } from '@/Components/icons';
import Snackbar from '@/Components/Snackbar';
import TextField from '@/Components/inputs/TextField';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

// Microcopy de câmera — espelha o spec de tela §5 (design/screens/STORY-009-*).
const ERRO_CAMERA = {
    permission: 'Não conseguimos acessar a câmera. Cole o link da nota.',
    nocamera: 'Nenhuma câmera encontrada. Cole o link da nota.',
    unsupported: 'Seu navegador não abriu a câmera aqui. Cole o link da nota.',
};

// Agrupa a chave de 44 dígitos em blocos de 4 para leitura (só exibição).
function formatarChave(chave) {
    return chave ? chave.replace(/(.{4})/g, '$1 ').trim() : '';
}

export default function Captura({ entradaInicial }) {
    const { flash } = usePage().props;
    const coleta = flash?.coleta ?? null;

    // 'idle' (escolher) | 'scan' (câmera ativa) | 'paste' (colar link).
    const [modo, setModo] = useState(entradaInicial ? 'paste' : 'idle');
    const [erroCamera, setErroCamera] = useState(null);

    const form = useForm({ entrada: entradaInicial ?? '', origem: 'scan' });
    const { data, setData, post, processing, errors, clearErrors } = form;

    const enviar = (entrada, origem) => {
        clearErrors();
        form.transform(() => ({ entrada, origem }));
        post('/coletar', { preserveScroll: true });
    };

    const aoDetectar = (texto) => {
        setModo('idle');
        enviar(texto, 'scan');
    };

    const aoErroCamera = (kind) => {
        setErroCamera(kind);
        setModo('paste');
    };

    const enviarColado = (e) => {
        e.preventDefault();
        enviar(data.entrada, 'compartilhado');
    };

    const recomecar = () => router.visit('/coletar');

    // --- Confirmação (capturado ou duplicado) ---------------------------------
    if (coleta && (coleta.situacao === 'capturado' || coleta.situacao === 'duplicado')) {
        const novo = coleta.situacao === 'capturado';
        return (
            <Shell>
                <Card
                    variant="content"
                    className="flex flex-col items-center gap-lg text-center shadow-elev-2"
                    data-testid="screen-captura-success"
                    role="status"
                >
                    <span className="text-positive">
                        <ReceiptIcon className="h-3xl w-3xl" />
                    </span>
                    <h1 className="font-display text-display-sm text-ink">
                        {novo ? 'Cupom capturado!' : 'Você já tinha enviado esse cupom'}
                    </h1>
                    <Badge variant={novo ? 'positive' : 'info'} data-testid="screen-captura-badge">
                        {novo ? 'Cupom recebido' : 'Cada nota conta uma vez'}
                    </Badge>
                    <p className="text-body-md text-body">
                        {novo
                            ? 'Recebemos sua nota. Vamos validar e confirmar em instantes.'
                            : 'Esse cupom já está na sua contribuição. Cada nota conta uma vez só.'}
                    </p>
                    {coleta.chave && (
                        <p className="break-all text-caption text-mute">
                            {formatarChave(coleta.chave)}
                        </p>
                    )}
                    <Button
                        variant="primary"
                        onClick={recomecar}
                        data-testid="screen-captura-another-btn"
                    >
                        Enviar outro cupom
                    </Button>
                </Card>
            </Shell>
        );
    }

    // --- Captura ---------------------------------------------------------------
    return (
        <Shell>
            <Card variant="content" className="flex flex-col gap-xl shadow-elev-2">
                <header className="flex flex-col gap-xs text-center">
                    <span className="mx-auto text-ink-deep">
                        <QrIcon className="h-3xl w-3xl" />
                    </span>
                    <h1
                        data-testid="screen-captura-title"
                        className="font-display text-display-sm text-ink"
                    >
                        Capturar cupom
                    </h1>
                    <p className="text-body-md text-body">
                        Escaneie o QR Code da sua nota ou cole o link.
                    </p>
                </header>

                {erroCamera && (
                    <Snackbar variant="warning" data-testid="screen-captura-camera-error">
                        {ERRO_CAMERA[erroCamera]}
                    </Snackbar>
                )}

                {modo === 'scan' ? (
                    <div className="flex flex-col gap-md">
                        <QrScanner onDetected={aoDetectar} onError={aoErroCamera} />
                        <p className="text-center text-body-sm text-body" aria-live="polite">
                            Aponte a câmera para o QR Code da NFC-e.
                        </p>
                        <Button
                            variant="secondary"
                            onClick={() => setModo('idle')}
                            data-testid="screen-captura-cancel-btn"
                        >
                            Cancelar
                        </Button>
                    </div>
                ) : (
                    <div className="flex flex-col gap-lg">
                        <Button
                            variant="primary"
                            onClick={() => {
                                setErroCamera(null);
                                setModo('scan');
                            }}
                            data-testid="screen-captura-scan-btn"
                        >
                            <QrIcon className="h-lg w-lg" />
                            Escanear QR Code
                        </Button>

                        <form onSubmit={enviarColado} className="flex flex-col gap-lg">
                            <TextField
                                label="Link ou chave da nota"
                                value={data.entrada}
                                onChange={(e) => setData('entrada', e.target.value)}
                                error={errors.entrada}
                                data-testid="screen-captura-paste-field"
                                errorTestId="screen-captura-error-entrada"
                                inputMode="text"
                                autoComplete="off"
                            />
                            <Button
                                type="submit"
                                variant={modo === 'paste' || data.entrada ? 'primary' : 'secondary'}
                                loading={processing}
                                disabled={!data.entrada}
                                data-testid="screen-captura-submit-btn"
                            >
                                Enviar cupom
                            </Button>
                        </form>
                    </div>
                )}
            </Card>
        </Shell>
    );
}

function Shell({ children }) {
    return (
        <>
            <Head title="Capturar cupom" />
            <main
                data-testid="screen-captura"
                className="flex min-h-screen flex-col items-center justify-center gap-lg bg-canvas-soft px-lg py-2xl"
            >
                <div className="w-full max-w-md">{children}</div>
            </main>
        </>
    );
}
