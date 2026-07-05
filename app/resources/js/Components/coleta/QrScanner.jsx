import { useCallback, useEffect, useRef, useState } from 'react';
import Button from '@/Components/Button';
import { CameraIcon, FlashIcon } from '@/Components/icons';

/**
 * QrScanner — leitura do QR da NFC-e pela câmera, robustecida para impressão
 * térmica ruim (STORY-009; reforço pós-campo: cupom desbotado/denso não lê).
 *
 * A assinatura do cupom (`p=chave|versão|amb|token|hash`) só existe DENTRO do QR —
 * sem ler o QR não há extração. Motor de leitura, do mais ao menos tolerante:
 *   1. `jsQR` (canvas) — robusto para QR fotografado; roda ao vivo e na foto.
 *   2. `BarcodeDetector` nativo (Android Chrome) — rápido, quando existe.
 *   3. `@zxing/browser` (import dinâmico, TRY_HARDER) — último recurso na foto.
 *
 * "Tirar foto" congela o quadro atual do vídeo num canvas (drawImage, síncrono) e
 * tenta muitas variantes: recorte na mira, com/sem realce de contraste, em escala
 * menor, invertendo. A foto fica visível e, se não ler, mostra um caminho honesto
 * ("não foi possível ler") + "Tentar de novo".
 *
 * Diagnóstico: quando não lê, dispara em background um OCR da chave de 44 dígitos e
 * registra a tentativa no backend (`/coleta/ilegivel`) para diagnóstico futuro. Isso
 * NÃO muda o que o usuário vê — para ele, segue "não foi possível ler".
 *
 * Props:
 *  - onDetected(text): conteúdo do QR (a URL/`p=` assinado da NFC-e).
 *  - onError(kind): 'permission' | 'nocamera' | 'unsupported'.
 */
export default function QrScanner({ onDetected, onError }) {
    const videoRef = useRef(null);
    const streamRef = useRef(null);
    const zxingRef = useRef(null);
    const jsqrRef = useRef(null);
    const ativoRef = useRef(false);
    const emitidoRef = useRef(false);
    const onDetectedRef = useRef(onDetected);
    const onErrorRef = useRef(onError);
    onDetectedRef.current = onDetected;
    onErrorRef.current = onError;

    const [temLanterna, setTemLanterna] = useState(false);
    const [lanternaLigada, setLanternaLigada] = useState(false);
    // 'nenhum' (mirando) | 'lendo' (foto congelada, decodificando) | 'falhou' (não leu)
    const [estadoFoto, setEstadoFoto] = useState('nenhum');
    const [fotoUrl, setFotoUrl] = useState(null);

    const suportaDetectorNativo = () =>
        typeof window !== 'undefined' && 'BarcodeDetector' in window;

    const carregarJsQR = useCallback(async () => {
        if (!jsqrRef.current) {
            const mod = await import('jsqr');
            jsqrRef.current = mod.default ?? mod;
        }
        return jsqrRef.current;
    }, []);

    const pararCamera = useCallback(() => {
        zxingRef.current?.stop();
        zxingRef.current = null;
        streamRef.current?.getTracks().forEach((t) => t.stop());
        streamRef.current = null;
        setLanternaLigada(false);
    }, []);

    const emitir = useCallback(
        (texto) => {
            if (emitidoRef.current || !texto) return;
            emitidoRef.current = true;
            pararCamera();
            onDetectedRef.current(texto);
        },
        [pararCamera],
    );

    // --- Canvas helpers -------------------------------------------------------
    const recortar = (fonte, sx, sy, sl, dl) => {
        const canvas = document.createElement('canvas');
        canvas.width = dl;
        canvas.height = dl;
        canvas.getContext('2d').drawImage(fonte, sx, sy, sl, sl, 0, 0, dl, dl);
        return canvas;
    };

    const desenhar = (fonte, largura, altura) => {
        const canvas = document.createElement('canvas');
        canvas.width = largura;
        canvas.height = altura;
        canvas.getContext('2d').drawImage(fonte, 0, 0, largura, altura);
        return canvas;
    };

    const imageDataDe = (canvas) => canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);

    // Realça contraste (cinza + esticão) — ajuda em QR de papel térmico desbotado.
    const realcarContraste = (origem) => {
        const canvas = desenhar(origem, origem.width, origem.height);
        const ctx = canvas.getContext('2d');
        const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const d = img.data;
        for (let i = 0; i < d.length; i += 4) {
            const y = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
            let v = (y - 128) * 1.8 + 128;
            v = v < 0 ? 0 : v > 255 ? 255 : v;
            d[i] = d[i + 1] = d[i + 2] = v;
        }
        ctx.putImageData(img, 0, 0);
        return canvas;
    };

    // Quadro central (maior quadrado central) — foca no que está na mira.
    const capturarCentral = () => {
        const video = videoRef.current;
        if (!video?.videoWidth) return null;
        const vw = video.videoWidth;
        const vh = video.videoHeight;
        const lado = Math.min(vw, vh);
        return recortar(video, (vw - lado) / 2, (vh - lado) / 2, lado, lado);
    };

    // Quadro inteiro do vídeo.
    const capturarQuadro = () => {
        const video = videoRef.current;
        if (!video?.videoWidth) return null;
        return desenhar(video, video.videoWidth, video.videoHeight);
    };

    // --- Decode ---------------------------------------------------------------
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
            return null;
        }
    };

    // Tenta muitas variantes na foto congelada: jsQR (recorte/inteiro, contraste,
    // escala, invertendo) e, por fim, zxing TRY_HARDER.
    const lerNaFoto = async (canvasFull) => {
        const jsQR = await carregarJsQR();
        const w = canvasFull.width;
        const h = canvasFull.height;
        const lado = Math.min(w, h);
        const central = recortar(canvasFull, (w - lado) / 2, (h - lado) / 2, lado, lado);

        const variantes = [
            central,
            realcarContraste(central),
            desenhar(central, Math.round(central.width / 2), Math.round(central.height / 2)),
            canvasFull,
            realcarContraste(canvasFull),
        ];

        for (const cv of variantes) {
            if (emitidoRef.current) return null;
            try {
                const id = imageDataDe(cv);
                const r = jsQR(id.data, id.width, id.height, { inversionAttempts: 'attemptBoth' });
                if (r?.data) return r.data;
            } catch {
                /* variante ruim; tenta a próxima. */
            }
        }

        const z = await lerCanvasComZxing(central);
        if (z) return z;
        return lerCanvasComZxing(realcarContraste(central));
    };

    // --- Diagnóstico de captura ilegível (background, invisível ao usuário) ----
    // Dígito verificador mod-11 (mesma regra de App\Domain\Coleta\ChaveAcesso).
    const dvValido = (chave44) => {
        const pesos = [2, 3, 4, 5, 6, 7, 8, 9];
        const inv = chave44.slice(0, 43).split('').reverse();
        let soma = 0;
        for (let i = 0; i < inv.length; i++) soma += Number(inv[i]) * pesos[i % 8];
        const resto = soma % 11;
        const dv = resto <= 1 ? 0 : 11 - resto;
        return dv === Number(chave44[43]);
    };

    const extrairChaveValida = (texto) => {
        const digitos = (texto.match(/\d/g) ?? []).join('');
        for (let i = 0; i + 44 <= digitos.length; i++) {
            const cand = digitos.slice(i, i + 44);
            if (dvValido(cand)) return cand;
        }
        return null;
    };

    // OCR só de dígitos (a chave costuma estar impressa acima do QR). Best-effort.
    const ocrDigitos = async (canvas) => {
        const { createWorker } = await import('tesseract.js');
        const worker = await createWorker('eng');
        try {
            await worker.setParameters({ tessedit_char_whitelist: '0123456789 ' });
            const { data } = await worker.recognize(canvas);
            return data?.text ?? '';
        } finally {
            await worker.terminate();
        }
    };

    // Registra a captura ilegível para diagnóstico futuro — com a chave se o OCR pegou.
    // Fire-and-forget: nunca afeta o que o usuário vê.
    const reportarIlegivel = async (canvas) => {
        let chave = null;
        try {
            const texto = await Promise.race([
                ocrDigitos(canvas),
                new Promise((resolve) => setTimeout(() => resolve(''), 20000)),
            ]);
            chave = extrairChaveValida(texto);
        } catch {
            /* OCR indisponível/falhou — registra a falha sem chave. */
        }
        try {
            await window.axios?.post('/coleta/ilegivel', { chave });
        } catch {
            /* telemetria best-effort. */
        }
    };

    // --- Câmera ao vivo -------------------------------------------------------
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
            if (nome === 'NotAllowedError' || nome === 'SecurityError') onErrorRef.current('permission');
            else if (nome === 'NotFoundError' || nome === 'OverconstrainedError' || nome === 'DevicesNotFoundError')
                onErrorRef.current('nocamera');
            else onErrorRef.current('unsupported');
            return;
        }

        if (!ativoRef.current || emitidoRef.current) {
            stream.getTracks().forEach((t) => t.stop());
            return;
        }
        streamRef.current = stream;

        const track = stream.getVideoTracks()[0];
        const caps = track?.getCapabilities?.() ?? {};
        setTemLanterna(Boolean(caps.torch));
        if (Array.isArray(caps.focusMode) && caps.focusMode.includes('continuous')) {
            try {
                await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
            } catch {
                /* alguns aparelhos recusam. */
            }
        }

        const video = videoRef.current;
        video.srcObject = stream;
        video.setAttribute('playsinline', 'true');
        try {
            await video.play();
        } catch {
            /* autoplay pode falhar sem gesto. */
        }

        const jsQR = await carregarJsQR();
        const detector = suportaDetectorNativo() ? new window.BarcodeDetector({ formats: ['qr_code'] }) : null;

        // Loop ao vivo: detector nativo (rápido) e/ou jsQR no quadro central.
        const tick = async () => {
            if (!ativoRef.current || emitidoRef.current || !streamRef.current) return;
            try {
                if (detector) {
                    const codigos = await detector.detect(video);
                    if (codigos?.length) return emitir(codigos[0].rawValue);
                }
                const central = capturarCentral();
                if (central) {
                    const id = imageDataDe(central);
                    const r = jsQR(id.data, id.width, id.height, { inversionAttempts: 'dontInvert' });
                    if (r?.data) return emitir(r.data);
                }
            } catch {
                /* frame ruim; tenta o próximo. */
            }
            if (ativoRef.current && !emitidoRef.current && streamRef.current) setTimeout(tick, 250);
        };
        setTimeout(tick, 300);
    }, [emitir, carregarJsQR]);

    useEffect(() => {
        ativoRef.current = true;
        iniciarCamera();
        return () => {
            ativoRef.current = false;
            pararCamera();
        };
    }, [iniciarCamera, pararCamera]);

    // --- Ações ----------------------------------------------------------------
    const alternarLanterna = async () => {
        const track = streamRef.current?.getVideoTracks?.()[0];
        if (!track) return;
        const alvo = !lanternaLigada;
        try {
            await track.applyConstraints({ advanced: [{ torch: alvo }] });
            setLanternaLigada(alvo);
        } catch {
            /* aparelho recusou. */
        }
    };

    const capturarEDecodificar = async () => {
        if (emitidoRef.current || estadoFoto === 'lendo') return;
        const canvas = capturarQuadro();
        if (!canvas) {
            setFotoUrl(null);
            setEstadoFoto('falhou');
            return;
        }
        // Congela a imagem na tela (miniatura leve) para o usuário ver o que capturou.
        setFotoUrl(desenhar(canvas, Math.round(canvas.width / 3), Math.round(canvas.height / 3)).toDataURL('image/jpeg', 0.7));
        setEstadoFoto('lendo');
        try {
            const texto = await Promise.race([
                lerNaFoto(canvas),
                new Promise((resolve) => setTimeout(() => resolve('__timeout__'), 12000)),
            ]);
            if (texto && texto !== '__timeout__') {
                emitir(texto);
                return;
            }
            setEstadoFoto('falhou');
            reportarIlegivel(canvas); // diagnóstico em background — não trava a UX.
        } catch {
            setEstadoFoto('falhou');
            reportarIlegivel(canvas);
        }
    };

    const tentarDeNovo = () => {
        setEstadoFoto('nenhum');
        setFotoUrl(null);
    };

    const congelado = estadoFoto !== 'nenhum';

    const Lanterna = () =>
        temLanterna && (
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
        );

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

                {/* Foto congelada por cima do vídeo — o usuário vê o que foi capturado. */}
                {congelado && fotoUrl && (
                    <img
                        src={fotoUrl}
                        alt="Imagem capturada"
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                )}

                {/* Moldura de mira. */}
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute inset-lg rounded-lg border-2 border-primary"
                />

                {estadoFoto === 'lendo' && (
                    <div className="absolute inset-0 flex items-center justify-center bg-ink/50" role="status">
                        <span className="rounded-lg bg-ink px-md py-sm text-body-sm font-semibold text-canvas">
                            Lendo o QR…
                        </span>
                    </div>
                )}

                {estadoFoto === 'falhou' && (
                    <div
                        className="absolute inset-0 flex flex-col items-center justify-center gap-xs bg-ink/60 p-lg text-center"
                        role="status"
                        data-testid="screen-captura-photo-failed"
                    >
                        <p className="text-body-md font-semibold text-canvas">
                            Não foi possível ler o QR desta nota.
                        </p>
                        <p className="text-body-sm text-canvas/80">
                            A impressão pode estar danificada. Aproxime, encha o quadro verde e tente de novo.
                        </p>
                    </div>
                )}
            </div>

            {estadoFoto === 'falhou' ? (
                <div className="flex gap-md">
                    <Lanterna />
                    <Button
                        variant="primary"
                        onClick={tentarDeNovo}
                        className="flex-1"
                        data-testid="screen-captura-retry-btn"
                    >
                        <CameraIcon className="h-lg w-lg" />
                        Tentar de novo
                    </Button>
                </div>
            ) : (
                <div className="flex gap-md">
                    <Lanterna />
                    <Button
                        variant="secondary"
                        onClick={capturarEDecodificar}
                        loading={estadoFoto === 'lendo'}
                        className="flex-1"
                        data-testid="screen-captura-photo-btn"
                    >
                        <CameraIcon className="h-lg w-lg" />
                        Tirar foto
                    </Button>
                </div>
            )}
        </div>
    );
}
