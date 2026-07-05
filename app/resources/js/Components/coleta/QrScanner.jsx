import { useCallback, useEffect, useRef, useState } from 'react';
import Button from '@/Components/Button';
import { CameraIcon, FlashIcon } from '@/Components/icons';

/**
 * QrScanner — leitura do QR da NFC-e pela câmera, robustecida para impressão
 * térmica ruim (STORY-009; reforço pós-campo: cupom desbotado/brilhoso não lê).
 *
 * A assinatura do cupom (`p=chave|versão|amb|token|hash`) só existe DENTRO do QR —
 * não há como recuperá-la se o QR não for lido. Por isso o foco é ler o QR de fato,
 * do decodificador mais tolerante ao mais compatível:
 *   1. `BarcodeDetector` nativo (Android Chrome) — decodifica QR sujo/torto muito
 *      melhor e mais barato que JS puro.
 *   2. `@zxing/browser` por import dinâmico (iOS Safari e afins) — fica fora do
 *      bundle inicial; só baixa quando a câmera abre (IDR-003).
 *
 * A câmera é aberta com constraints próprias (traseira + alta resolução + foco
 * contínuo) para termos acesso ao track e oferecer **lanterna** (mata o reflexo do
 * papel térmico, a maior causa de falha) e **tirar foto** (uma foto estática de alta
 * resolução, com o autofoco/HDR da câmera nativa, lê onde o vídeo ao vivo falha).
 *
 * Importante: a câmera nativa da foto e o stream ao vivo disputam o mesmo hardware —
 * abrir a foto com o vídeo ativo faz a captura voltar vazia em muitos aparelhos. Por
 * isso liberamos o stream antes da foto e o retomamos se a foto não decodificar.
 *
 * Falha de câmera vira `onError(kind)` para a página cair no caminho de colar (CA-2).
 *
 * Props:
 *  - onDetected(text): conteúdo do QR (a URL/`p=` assinado da NFC-e).
 *  - onError(kind): 'permission' | 'nocamera' | 'unsupported'.
 */
export default function QrScanner({ onDetected, onError }) {
    const videoRef = useRef(null);
    const inputFotoRef = useRef(null);
    const streamRef = useRef(null);
    const zxingRef = useRef(null);
    const ativoRef = useRef(false);
    const emitidoRef = useRef(false);
    // Refs de callback: mantêm o efeito de montagem estável (roda uma vez só).
    const onDetectedRef = useRef(onDetected);
    const onErrorRef = useRef(onError);
    onDetectedRef.current = onDetected;
    onErrorRef.current = onError;

    const [temLanterna, setTemLanterna] = useState(false);
    const [lanternaLigada, setLanternaLigada] = useState(false);
    const [decodificandoFoto, setDecodificandoFoto] = useState(false);
    const [avisoFoto, setAvisoFoto] = useState(false);
    const [segundos, setSegundos] = useState(0);

    const suportaDetectorNativo = () =>
        typeof window !== 'undefined' && 'BarcodeDetector' in window;

    // Libera o hardware da câmera (sem encerrar o componente).
    const pararCamera = useCallback(() => {
        zxingRef.current?.stop();
        zxingRef.current = null;
        streamRef.current?.getTracks().forEach((t) => t.stop());
        streamRef.current = null;
        setLanternaLigada(false);
    }, []);

    // Emite o resultado uma vez só e encerra a câmera.
    const emitir = useCallback(
        (texto) => {
            if (emitidoRef.current || !texto) return;
            emitidoRef.current = true;
            pararCamera();
            onDetectedRef.current(texto);
        },
        [pararCamera],
    );

    // Abre a câmera e inicia a leitura contínua. Reentrante: pode ser chamada de novo
    // para retomar o vídeo depois de uma tentativa de foto que não decodificou.
    const iniciarCamera = useCallback(async () => {
        if (!ativoRef.current || emitidoRef.current || streamRef.current) return;

        const midia = navigator.mediaDevices;
        if (!midia?.getUserMedia) {
            onErrorRef.current('unsupported');
            return;
        }

        let stream;
        try {
            stream = await midia.getUserMedia({
                audio: false,
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                },
            });
        } catch (e) {
            if (!ativoRef.current) return;
            const nome = e?.name ?? '';
            if (nome === 'NotAllowedError' || nome === 'SecurityError') {
                onErrorRef.current('permission');
            } else if (
                nome === 'NotFoundError' ||
                nome === 'OverconstrainedError' ||
                nome === 'DevicesNotFoundError'
            ) {
                onErrorRef.current('nocamera');
            } else {
                onErrorRef.current('unsupported');
            }
            return;
        }

        if (!ativoRef.current || emitidoRef.current) {
            stream.getTracks().forEach((t) => t.stop());
            return;
        }
        streamRef.current = stream;

        // Capacidades do track: lanterna e foco contínuo (quando o aparelho expõe).
        const track = stream.getVideoTracks()[0];
        const caps = track?.getCapabilities?.() ?? {};
        setTemLanterna(Boolean(caps.torch));
        if (Array.isArray(caps.focusMode) && caps.focusMode.includes('continuous')) {
            try {
                await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
            } catch {
                /* alguns aparelhos recusam; segue sem foco contínuo. */
            }
        }

        const video = videoRef.current;

        if (suportaDetectorNativo()) {
            video.srcObject = stream;
            video.setAttribute('playsinline', 'true');
            try {
                await video.play();
            } catch {
                /* autoplay pode falhar sem gesto; a leitura ainda ocorre. */
            }
            const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
            const tick = async () => {
                if (!ativoRef.current || emitidoRef.current || !streamRef.current) return;
                try {
                    const codigos = await detector.detect(video);
                    if (codigos?.length) {
                        emitir(codigos[0].rawValue);
                        return;
                    }
                } catch {
                    /* frame ruim (sem foco/estabilidade); tenta o próximo. */
                }
                if (ativoRef.current && !emitidoRef.current && streamRef.current) {
                    setTimeout(tick, 200);
                }
            };
            setTimeout(tick, 200);
        } else {
            try {
                const { BrowserQRCodeReader } = await import('@zxing/browser');
                const leitor = new BrowserQRCodeReader();
                zxingRef.current = await leitor.decodeFromStream(stream, video, (res) => {
                    if (res) emitir(res.getText());
                });
            } catch {
                if (ativoRef.current) onErrorRef.current('unsupported');
            }
        }
    }, [emitir]);

    // Ciclo de vida: abre a câmera ao montar, libera ao desmontar.
    useEffect(() => {
        ativoRef.current = true;
        iniciarCamera();
        return () => {
            ativoRef.current = false;
            pararCamera();
        };
    }, [iniciarCamera, pararCamera]);

    // Conta o tempo tentando para sugerir lanterna/foto quando está difícil.
    useEffect(() => {
        const id = setInterval(() => setSegundos((s) => s + 1), 1000);
        return () => clearInterval(id);
    }, []);

    const alternarLanterna = async () => {
        const track = streamRef.current?.getVideoTracks?.()[0];
        if (!track) return;
        const alvo = !lanternaLigada;
        try {
            await track.applyConstraints({ advanced: [{ torch: alvo }] });
            setLanternaLigada(alvo);
        } catch {
            /* aparelho recusou; mantém o estado atual. */
        }
    };

    // Abre a câmera nativa para a foto. Libera antes o stream ao vivo — senão a câmera
    // fica ocupada e a captura volta vazia (foi o que aconteceu em campo).
    const abrirCaptura = () => {
        setAvisoFoto(false);
        pararCamera();
        inputFotoRef.current?.click();
    };

    // Decodifica a foto estática (autofoco/HDR da câmera nativa) — lê onde o vídeo falha.
    const decodificarFoto = async (arquivo) => {
        if (emitidoRef.current) return;
        // Foto cancelada (voltou sem arquivo): retoma o vídeo ao vivo.
        if (!arquivo) {
            iniciarCamera();
            return;
        }

        setAvisoFoto(false);
        setDecodificandoFoto(true);
        const url = URL.createObjectURL(arquivo);
        try {
            if (suportaDetectorNativo()) {
                const bitmap = await createImageBitmap(arquivo);
                const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
                const codigos = await detector.detect(bitmap);
                bitmap.close?.();
                if (codigos?.length) {
                    emitir(codigos[0].rawValue);
                    return;
                }
            } else {
                const { BrowserQRCodeReader } = await import('@zxing/browser');
                const leitor = new BrowserQRCodeReader();
                const res = await leitor.decodeFromImageUrl(url);
                if (res) {
                    emitir(res.getText());
                    return;
                }
            }
            // Não achou o QR na foto: avisa e retoma o vídeo ao vivo.
            setAvisoFoto(true);
            iniciarCamera();
        } catch {
            setAvisoFoto(true);
            iniciarCamera();
        } finally {
            URL.revokeObjectURL(url);
            setDecodificandoFoto(false);
        }
    };

    return (
        <div className="flex flex-col gap-md">
            <div className="relative overflow-hidden rounded-xl bg-ink">
                <video
                    ref={videoRef}
                    data-testid="screen-captura-video"
                    className="aspect-square w-full object-cover"
                    playsInline
                    muted
                    aria-label="Câmera para escanear o QR Code da nota"
                />
                {/* Moldura de mira — apoio visual, decorativa. */}
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute inset-lg rounded-lg border-2 border-primary"
                />
            </div>

            <div className="flex gap-md">
                {temLanterna && (
                    <Button
                        variant={lanternaLigada ? 'primary' : 'secondary'}
                        onClick={alternarLanterna}
                        className="flex-1"
                        data-testid="screen-captura-torch-btn"
                        aria-pressed={lanternaLigada}
                    >
                        <FlashIcon className="h-lg w-lg" />
                        {lanternaLigada ? 'Desligar luz' : 'Lanterna'}
                    </Button>
                )}
                <Button
                    variant="secondary"
                    onClick={abrirCaptura}
                    loading={decodificandoFoto}
                    className="flex-1"
                    data-testid="screen-captura-photo-btn"
                >
                    <CameraIcon className="h-lg w-lg" />
                    Tirar foto
                </Button>
                <input
                    ref={inputFotoRef}
                    type="file"
                    accept="image/*"
                    capture="environment"
                    className="hidden"
                    data-testid="screen-captura-photo-input"
                    onChange={(e) => {
                        const arquivo = e.target.files?.[0];
                        e.target.value = ''; // permite tirar outra foto igual em seguida
                        decodificarFoto(arquivo);
                    }}
                />
            </div>

            {avisoFoto ? (
                <p className="text-center text-body-sm text-body" role="status">
                    Não deu pra ler o QR nessa foto. Aproxime, ligue a lanterna e tente de novo.
                </p>
            ) : decodificandoFoto ? (
                <p className="text-center text-body-sm text-mute" role="status">
                    Lendo a foto…
                </p>
            ) : (
                segundos >= 6 && (
                    <p className="text-center text-body-sm text-mute" aria-live="polite">
                        Está difícil? Ligue a lanterna ou toque em “Tirar foto”.
                    </p>
                )
            )}
        </div>
    );
}
