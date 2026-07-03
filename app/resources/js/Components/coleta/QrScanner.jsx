import { useEffect, useRef } from 'react';

/**
 * QrScanner — leitura do QR da NFC-e pela câmera (STORY-009, CA-1).
 *
 * Decodifica com `@zxing/browser` carregado por **import dinâmico** (fica fora do
 * bundle inicial; só baixa quando o usuário abre a câmera). Funciona em Android e
 * iOS Safari. Falha de permissão/câmera vira `onError(kind)` para a página cair no
 * caminho de colar link (CA-2). Encerra a câmera ao desmontar.
 *
 * Props:
 *  - onDetected(text): chamado com o conteúdo do QR (a URL/chave da NFC-e).
 *  - onError(kind): 'permission' | 'nocamera' | 'unsupported'.
 */
export default function QrScanner({ onDetected, onError }) {
    const videoRef = useRef(null);

    useEffect(() => {
        let controls = null;
        let cancelado = false;

        (async () => {
            try {
                const { BrowserQRCodeReader } = await import('@zxing/browser');
                const leitor = new BrowserQRCodeReader();
                controls = await leitor.decodeFromVideoDevice(
                    undefined,
                    videoRef.current,
                    (resultado) => {
                        if (resultado && !cancelado) {
                            cancelado = true;
                            controls?.stop();
                            onDetected(resultado.getText());
                        }
                    },
                );
            } catch (e) {
                if (cancelado) return;
                const nome = e?.name ?? '';
                if (nome === 'NotAllowedError' || nome === 'SecurityError') {
                    onError('permission');
                } else if (nome === 'NotFoundError' || nome === 'OverconstrainedError') {
                    onError('nocamera');
                } else {
                    onError('unsupported');
                }
            }
        })();

        return () => {
            cancelado = true;
            controls?.stop();
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return (
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
    );
}
