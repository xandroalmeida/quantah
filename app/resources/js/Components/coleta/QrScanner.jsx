import { useCallback, useEffect, useRef, useState } from 'react';
import Button from '@/Components/Button';
import { CameraIcon, FlashIcon } from '@/Components/icons';

/**
 * QrScanner — leitura do QR da NFC-e pela câmera, robustecida para impressão
 * térmica ruim (STORY-009; reforço pós-campo: cupom desbotado/brilhoso não lê).
 *
 * A assinatura do cupom (`p=chave|versão|amb|token|hash`) só existe DENTRO do QR —
 * não há como recuperá-la se o QR não for lido. Foco: ler o QR de fato.
 *   1. `BarcodeDetector` nativo (Android Chrome) no vídeo ao vivo — rápido.
 *   2. `@zxing/browser` por import dinâmico (iOS Safari e afins).
 *
 * "Tirar foto" congela o quadro atual do vídeo num canvas de forma síncrona
 * (drawImage não trava) e decodifica com mais esforço (zxing TRY_HARDER + realce de
 * contraste). NÃO usa a captura de foto do track (que pendura em vários Androids) nem
 * abre a câmera nativa (input capture — saía do app e disputava o hardware).
 *
 * DIAGNÓSTICO: enquanto investigamos a foto que "não faz nada", há um painel visível
 * que registra cada passo e qualquer erro (nome + mensagem). Remover depois de fechar.
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
    const onDetectedRef = useRef(onDetected);
    const onErrorRef = useRef(onError);
    onDetectedRef.current = onDetected;
    onErrorRef.current = onError;

    const [temLanterna, setTemLanterna] = useState(false);
    const [lanternaLigada, setLanternaLigada] = useState(false);
    const [decodificandoFoto, setDecodificandoFoto] = useState(false);
    const [avisoFoto, setAvisoFoto] = useState(null);
    const [segundos, setSegundos] = useState(0);
    const [diag, setDiag] = useState([]);

    // Registra uma linha de diagnóstico visível (guarda as últimas 40).
    const registrar = useCallback((msg) => {
        const t = (performance.now() / 1000).toFixed(1);
        setDiag((d) => [...d.slice(-39), `${t}s ${msg}`]);
    }, []);

    const suportaDetectorNativo = () =>
        typeof window !== 'undefined' && 'BarcodeDetector' in window;

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

    const iniciarCamera = useCallback(async () => {
        if (!ativoRef.current || emitidoRef.current || streamRef.current) return;

        const midia = navigator.mediaDevices;
        if (!midia?.getUserMedia) {
            registrar('sem getUserMedia (navegador não suporta)');
            onErrorRef.current('unsupported');
            return;
        }

        registrar('abrindo câmera…');
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
            registrar('getUserMedia falhou: ' + nome);
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

        const track = stream.getVideoTracks()[0];
        const caps = track?.getCapabilities?.() ?? {};
        const cfg = track?.getSettings?.() ?? {};
        setTemLanterna(Boolean(caps.torch));
        registrar(
            `câmera ok ${cfg.width ?? '?'}x${cfg.height ?? '?'} ` +
                `torch=${Boolean(caps.torch)} detectorNativo=${suportaDetectorNativo()}`,
        );
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
                    /* frame ruim; tenta o próximo. */
                }
                if (ativoRef.current && !emitidoRef.current && streamRef.current) {
                    setTimeout(tick, 200);
                }
            };
            setTimeout(tick, 200);
        } else {
            registrar('modo zxing ao vivo (sem detector nativo)');
            try {
                const { BrowserQRCodeReader } = await import('@zxing/browser');
                const leitor = new BrowserQRCodeReader();
                zxingRef.current = await leitor.decodeFromStream(stream, video, (res) => {
                    if (res) emitir(res.getText());
                });
            } catch (e) {
                registrar('zxing ao vivo falhou: ' + (e?.name ?? e));
                if (ativoRef.current) onErrorRef.current('unsupported');
            }
        }
    }, [emitir, registrar]);

    useEffect(() => {
        ativoRef.current = true;
        registrar(`montado; detectorNativo=${suportaDetectorNativo()}`);
        iniciarCamera();
        return () => {
            ativoRef.current = false;
            pararCamera();
        };
    }, [iniciarCamera, pararCamera, registrar]);

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

    // Congela o quadro atual do vídeo num canvas — SÍNCRONO (drawImage não trava).
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
    const lerCanvasComZxing = async (canvas, rotulo) => {
        try {
            const { BrowserQRCodeReader } = await import('@zxing/browser');
            const { DecodeHintType } = await import('@zxing/library');
            const hints = new Map();
            hints.set(DecodeHintType.TRY_HARDER, true);
            const leitor = new BrowserQRCodeReader(hints);
            const res = await leitor.decodeFromImageUrl(canvas.toDataURL('image/png'));
            registrar(`zxing(${rotulo}): ${res ? 'ACHOU' : 'nada'}`);
            return res?.getText() ?? null;
        } catch (e) {
            registrar(`zxing(${rotulo}): ${e?.name ?? e}`); // NotFound = normal
            return null;
        }
    };

    const lerNoQuadro = async (canvas) => {
        if (suportaDetectorNativo()) {
            try {
                const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
                const codigos = await detector.detect(canvas);
                registrar(`detector nativo: ${codigos?.length ?? 0} código(s)`);
                if (codigos?.length) return codigos[0].rawValue;
            } catch (e) {
                registrar('detector nativo erro: ' + (e?.name ?? e));
            }
        }
        const cru = await lerCanvasComZxing(canvas, 'cru');
        if (cru) return cru;
        return lerCanvasComZxing(realcarContraste(canvas), 'contraste');
    };

    // "Tirar foto": congela o quadro e tenta ler com mais esforço. Cada passo é logado
    // no painel de diagnóstico; nunca fica mudo.
    const capturarEDecodificar = async () => {
        registrar(
            `clique foto: stream=${!!streamRef.current} ` +
                `video=${videoRef.current?.videoWidth ?? 0}x${videoRef.current?.videoHeight ?? 0} ` +
                `ocupado=${decodificandoFoto}`,
        );
        if (emitidoRef.current || decodificandoFoto) {
            registrar('ignorado (ocupado ou já emitido)');
            return;
        }
        setAvisoFoto(null);
        setDecodificandoFoto(true);
        try {
            const canvas = capturarQuadro();
            if (!canvas) {
                registrar('sem quadro: vídeo 0x0 (câmera não pronta)');
                setAvisoFoto('camera');
                return;
            }
            registrar(`quadro ${canvas.width}x${canvas.height} — decodificando…`);
            const texto = await Promise.race([
                lerNoQuadro(canvas),
                new Promise((resolve) => setTimeout(() => resolve('__timeout__'), 8000)),
            ]);
            if (texto === '__timeout__') {
                registrar('decode: TIMEOUT 8s');
                setAvisoFoto('erro');
                return;
            }
            if (texto) {
                registrar('QR lido — emitindo');
                emitir(texto);
                return;
            }
            registrar('decode: nenhum QR encontrado');
            setAvisoFoto(`nqr:${canvas.width}x${canvas.height}`);
        } catch (e) {
            registrar('EXCEÇÃO: ' + (e?.name ?? '') + ' ' + (e?.message ?? String(e)));
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
            return 'Algo falhou ao ler a foto. Veja o diagnóstico abaixo.';
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

            {/* Painel de diagnóstico (temporário) — mostra o passo a passo e os erros. */}
            {diag.length > 0 && (
                <pre
                    data-testid="screen-captura-diag"
                    className="max-h-40 overflow-auto whitespace-pre-wrap rounded-lg bg-ink/5 p-sm text-left font-mono text-[10px] leading-tight text-body"
                >
                    {diag.join('\n')}
                </pre>
            )}
        </div>
    );
}
