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
    // Painel de diagnóstico da leitura ao vivo (temporário — investigação iOS em campo).
    const [diag, setDiag] = useState(null);

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

    // Luma média (amostrada) — perto de 0 revela canvas preto (drawImage do vídeo falhou no iOS).
    const lumaMedia = (id) => {
        const d = id.data;
        let soma = 0;
        let n = 0;
        for (let i = 0; i < d.length; i += 64) {
            soma += 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
            n++;
        }
        return n ? Math.round(soma / n) : 0;
    };

    // Reduz o canvas para o maior lado <= max (jsQR/zxing gostam de módulos pequenos; e acelera).
    const reduzirPara = (canvas, max) => {
        const m = Math.max(canvas.width, canvas.height);
        if (m <= max) return canvas;
        const f = max / m;
        return desenhar(canvas, Math.round(canvas.width * f), Math.round(canvas.height * f));
    };

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

    // Limiarização adaptativa local (Bradley, via imagem integral): cada pixel é comparado à
    // MÉDIA da sua vizinhança, não a um limiar global. É o que resgata QR fotografado com
    // iluminação desigual/glare ou térmico desbotado — casos em que o jsQR (limiar global)
    // não acha os padrões de posição. O zxing fazia isso internamente (HybridBinarizer); aqui
    // fica explícito e barato, sem depender do decode contínuo do zxing.
    const binarizarAdaptativo = (origem) => {
        const canvas = desenhar(origem, origem.width, origem.height);
        const ctx = canvas.getContext('2d');
        const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const d = img.data;
        const w = canvas.width;
        const h = canvas.height;
        const cinza = new Float64Array(w * h);
        for (let i = 0, j = 0; i < d.length; i += 4, j++) {
            cinza[j] = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
        }
        // Imagem integral (soma acumulada) → média de qualquer janela em O(1).
        const integ = new Float64Array((w + 1) * (h + 1));
        for (let y = 0; y < h; y++) {
            let linha = 0;
            for (let x = 0; x < w; x++) {
                linha += cinza[y * w + x];
                integ[(y + 1) * (w + 1) + (x + 1)] = integ[y * (w + 1) + (x + 1)] + linha;
            }
        }
        const janela = Math.max(8, w >> 4);
        const meia = janela >> 1;
        const C = 7; // margem: pixel só vira preto se estiver claramente abaixo da média local
        for (let y = 0; y < h; y++) {
            for (let x = 0; x < w; x++) {
                const x1 = Math.max(0, x - meia);
                const y1 = Math.max(0, y - meia);
                const x2 = Math.min(w - 1, x + meia);
                const y2 = Math.min(h - 1, y + meia);
                const cnt = (x2 - x1 + 1) * (y2 - y1 + 1);
                const soma =
                    integ[(y2 + 1) * (w + 1) + (x2 + 1)] -
                    integ[y1 * (w + 1) + (x2 + 1)] -
                    integ[(y2 + 1) * (w + 1) + x1] +
                    integ[y1 * (w + 1) + x1];
                const v = cinza[y * w + x] < soma / cnt - C ? 0 : 255;
                const k = (y * w + x) * 4;
                d[k] = d[k + 1] = d[k + 2] = v;
            }
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
            binarizarAdaptativo(central),
            realcarContraste(central),
            desenhar(central, Math.round(central.width / 2), Math.round(central.height / 2)),
            canvasFull,
            binarizarAdaptativo(canvasFull),
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

        // jsQR num canvas com várias estratégias (cru → adaptativo → invertendo). Devolve o
        // texto e QUAL variante leu (para o diagnóstico), ou null.
        const lerCanvasJsQR = (canvas) => {
            const cru = imageDataDe(canvas);
            let r = jsQR(cru.data, cru.width, cru.height, { inversionAttempts: 'dontInvert' });
            if (r?.data) return { data: r.data, via: 'cru' };
            const bin = imageDataDe(binarizarAdaptativo(canvas));
            r = jsQR(bin.data, bin.width, bin.height, { inversionAttempts: 'attemptBoth' });
            if (r?.data) return { data: r.data, via: 'adapt' };
            return null;
        };

        let n = 0;
        // Loop ao vivo. Motores: BarcodeDetector nativo (Android) e, para todos, jsQR no recorte
        // central E no quadro inteiro reduzido (cobre QR pequeno/descentralizado no iOS), com
        // zxing periódico de reforço. Cada motor tem try/catch próprio — um detector nativo que
        // lança NÃO pode faminto o jsQR. Instrumentado: publica métricas no painel de diagnóstico.
        const tick = async () => {
            if (!ativoRef.current || emitidoRef.current || !streamRef.current) return;
            n++;
            const info = { n, vw: video.videoWidth || 0, vh: video.videoHeight || 0, det: !!detector, luma: -1, via: '—' };

            if (detector) {
                try {
                    const codigos = await detector.detect(video);
                    if (codigos?.length) return emitir(codigos[0].rawValue);
                } catch {
                    /* detector nativo instável neste aparelho; o jsQR abaixo assume. */
                }
            }
            try {
                const central = capturarCentral();
                if (central) {
                    info.luma = lumaMedia(imageDataDe(central));
                    const alvo = reduzirPara(central, 800);
                    let res = lerCanvasJsQR(alvo);
                    // Também o quadro INTEIRO reduzido — pega QR fora do quadrado central.
                    if (!res) {
                        const full = capturarQuadro();
                        if (full) res = lerCanvasJsQR(reduzirPara(full, 1000));
                        if (res) info.via += '/full';
                    }
                    // Reforço zxing (binarizador adaptativo próprio) a cada ~1s.
                    if (!res && n % 4 === 0) {
                        const z = await lerCanvasComZxing(alvo);
                        if (z) res = { data: z, via: 'zxing' };
                    }
                    if (res) {
                        info.via = res.via;
                        setDiag(info);
                        return emitir(res.data);
                    }
                }
            } catch (e) {
                info.via = 'erro:' + (e?.name ?? 'x');
            }
            setDiag(info);
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

                {/* Painel de diagnóstico (TEMPORÁRIO — investigação de leitura em campo no iOS). */}
                {diag && (
                    <pre
                        data-testid="screen-captura-diag"
                        className="pointer-events-none absolute left-1 top-1 m-0 rounded bg-ink/70 px-1 py-0.5 text-left font-mono text-[10px] leading-tight text-canvas"
                    >
                        {`#${diag.n} ${diag.vw}x${diag.vh} det:${diag.det ? 'sim' : 'nao'} luma:${diag.luma} via:${diag.via}`}
                    </pre>
                )}

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
