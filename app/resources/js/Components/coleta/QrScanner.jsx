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
 * papel térmico, a maior causa de falha) e **tirar foto** — que NÃO abre a câmera
 * nativa (isso saía do app e disputava o hardware, voltando vazio) NEM usa a captura
 * de foto do track (que pendura em vários Androids). Em vez disso congela o quadro
 * atual do vídeo num canvas de forma síncrona (drawImage, que não trava) e decodifica
 * com mais esforço (zxing TRY_HARDER + realce de contraste), lendo onde o loop ao vivo
 * não fecha. Sempre termina num desfecho visível — nunca fica "sem fazer nada".
 *
 * Falha de câmera vira `onError(kind)` para a página cair no caminho de colar (CA-2).
 *
 * Props:
 *  - onDetected(text): conteúdo do QR (a URL/`p=` assinado da NFC-e).
 *  - onError(kind): 'permission' | 'nocamera' | 'unsupported'.
 */
export default function QrScanner({ onDetected, onError }) {
    const videoRef = useRef(null);
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

    const desenharParaCanvas = (fonte, largura, altura) => {
        const canvas = document.createElement('canvas');
        canvas.width = largura;
        canvas.height = altura;
        canvas.getContext('2d').drawImage(fonte, 0, 0, largura, altura);
        return canvas;
    };

    // Captura o quadro atual do vídeo num canvas — SÍNCRONO (drawImage não trava).
    // Deliberadamente não usa a API de captura de foto do track (takePhoto/grabFrame):
    // ela pendura em vários Androids (o await nunca volta), e era o que fazia a foto
    // "não fazer nada".
    const capturarQuadro = () => {
        const video = videoRef.current;
        if (!video?.videoWidth) return null;
        return desenharParaCanvas(video, video.videoWidth, video.videoHeight);
    };

    // Realça contraste (cinza + esticão) — ajuda em QR de papel térmico desbotado.
    const realcarContraste = (origem) => {
        const canvas = desenharParaCanvas(origem, origem.width, origem.height);
        const ctx = canvas.getContext('2d');
        const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const d = img.data;
        for (let i = 0; i < d.length; i += 4) {
            const y = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
            let v = (y - 128) * 1.6 + 128;
            v = v < 0 ? 0 : v > 255 ? 255 : v;
            d[i] = d[i + 1] = d[i + 2] = v;
        }
        ctx.putImageData(img, 0, 0);
        return canvas;
    };

    // Decode esforçado com zxing (TRY_HARDER) sobre um canvas.
    const lerCanvasComZxing = async (canvas) => {
        try {
            const { BrowserQRCodeReader } = await import('@zxing/browser');
            const { DecodeHintType } = await import('@zxing/library');
            const hints = new Map();
            hints.set(DecodeHintType.TRY_HARDER, true);
            const leitor = new BrowserQRCodeReader(hints);
            const res = await leitor.decodeFromImageUrl(canvas.toDataURL('image/png'));
            return res?.getText() ?? null;
        } catch {
            return null; // NotFound e afins → não achou.
        }
    };

    // Lê o QR num quadro congelado, do mais rápido ao mais esforçado.
    const lerNoQuadro = async (canvas) => {
        if (suportaDetectorNativo()) {
            try {
                const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
                const codigos = await detector.detect(canvas);
                if (codigos?.length) return codigos[0].rawValue;
            } catch {
                /* segue para o zxing. */
            }
        }
        const cru = await lerCanvasComZxing(canvas);
        if (cru) return cru;
        return lerCanvasComZxing(realcarContraste(canvas));
    };

    // "Tirar foto": congela o quadro atual e tenta ler com mais esforço. Não pode travar
    // (captura síncrona) e sempre termina num desfecho visível (sucesso ou aviso).
    const capturarEDecodificar = async () => {
        if (emitidoRef.current || decodificandoFoto) return;
        setAvisoFoto(null);
        setDecodificandoFoto(true);
        try {
            const canvas = capturarQuadro();
            if (!canvas) {
                setAvisoFoto('camera');
                return;
            }
            // Rede de segurança: se o decode empacar, desiste em 8s e avisa.
            const texto = await Promise.race([
                lerNoQuadro(canvas),
                new Promise((resolve) => setTimeout(() => resolve(null), 8000)),
            ]);
            if (texto) {
                emitir(texto);
                return;
            }
            setAvisoFoto(`nqr:${canvas.width}x${canvas.height}`);
        } catch {
            setAvisoFoto('erro');
        } finally {
            setDecodificandoFoto(false);
        }
    };

    const mensagemAviso = (aviso) => {
        if (aviso === 'camera') {
            return 'A câmera ainda não está pronta. Espere o vídeo aparecer e tente de novo.';
        }
        if (aviso === 'erro') {
            return 'Algo falhou ao ler a foto. Tente de novo.';
        }
        const tam = typeof aviso === 'string' && aviso.startsWith('nqr:') ? ` (${aviso.slice(4)})` : '';
        return `Não encontrei o QR na imagem${tam}. Aproxime, encha o quadro e ligue a lanterna.`;
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
                    onClick={capturarEDecodificar}
                    loading={decodificandoFoto}
                    className="flex-1"
                    data-testid="screen-captura-photo-btn"
                >
                    <CameraIcon className="h-lg w-lg" />
                    Tirar foto
                </Button>
            </div>

            {avisoFoto ? (
                <p className="text-center text-body-sm text-body" role="status">
                    {mensagemAviso(avisoFoto)}
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
