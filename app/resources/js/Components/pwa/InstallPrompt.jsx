import Card from '@/Components/Card';
import Button from '@/Components/Button';
import { DownloadIcon, ShareIcon, XIcon } from '@/Components/icons';
import { useInstallPrompt } from '@/pwa/useInstallPrompt';
import { t } from '@/i18n';

/**
 * InstallPrompt — convite dispensável para instalar a PWA na home do Coletador
 * (STORY-038 · EPIC-008). Dois ramos:
 *  - Android/Chrome: botão que dispara o prompt nativo (beforeinstallprompt capturado);
 *  - iOS/Safari: instrução do "Adicionar à Tela de Início" (iOS não expõe API de instalação).
 *
 * Não aparece quando já instalado (standalone) ou quando o usuário dispensou (persistido).
 * Sobre o DS: card `feature-green`, verde como único accent, alvos ≥48px, foco visível, aria.
 */
export default function InstallPrompt() {
    const { mostrar, isIos, promptInstall, dispensar } = useInstallPrompt();

    if (!mostrar) {
        return null;
    }

    return (
        <Card
            variant="feature-green"
            className="relative flex items-start gap-md"
            role="region"
            aria-label={t('Install the Quantah app')}
            data-testid="pwa-install-prompt"
        >
            <span aria-hidden="true" className="mt-xxs text-ink-deep">
                <DownloadIcon className="h-xl w-xl" />
            </span>

            <div className="flex min-w-0 flex-1 flex-col gap-sm pr-2xl">
                <div className="flex flex-col gap-xxs">
                    <p className="text-body-md font-semibold text-ink">
                        {t('Install the Quantah app')}
                    </p>
                    <p className="text-body-sm text-body">
                        {isIos
                            ? t('On iPhone: tap Share, then Add to Home Screen.')
                            : t('Add it to your home screen for one-tap access.')}
                    </p>
                </div>

                {isIos ? (
                    <p className="flex items-center gap-xs text-body-sm font-semibold text-ink">
                        <ShareIcon className="h-lg w-lg" aria-hidden="true" />
                        {t('Share')} → {t('Add to Home Screen')}
                    </p>
                ) : (
                    <div>
                        <Button
                            variant="primary"
                            onClick={promptInstall}
                            data-testid="pwa-install-btn"
                        >
                            <DownloadIcon className="h-lg w-lg" />
                            {t('Install')}
                        </Button>
                    </div>
                )}
            </div>

            {/* Dispensar (alvo ≥48px). Posicionado no canto; não rouba o CTA verde. */}
            <button
                type="button"
                onClick={dispensar}
                aria-label={t('Not now')}
                data-testid="pwa-install-dismiss"
                className="absolute right-sm top-sm flex min-h-3xl min-w-3xl items-center justify-center rounded-full text-body hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink"
            >
                <XIcon className="h-lg w-lg" aria-hidden="true" />
            </button>
        </Card>
    );
}
