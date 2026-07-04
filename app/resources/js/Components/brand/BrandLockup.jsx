import { t } from '@/i18n';
import BrandMark from './BrandMark';

/**
 * BrandLockup — `brand.lockup` do DS (DDR-004): BrandMark + wordmark "Quantah" (Inter 900),
 * com tagline opcional. É o único lugar onde a marca aparece em escala numa tela.
 *
 * `onDark` inverte o wordmark/tagline para uso sobre superfície escura (painel de hero,
 * `card.feature-dark`). "Quantah" é nome próprio — não traduzido.
 */
export default function BrandLockup({ tagline = false, onDark = false, className = '' }) {
    const word = onDark ? 'text-canvas' : 'text-ink';
    const tag = onDark ? 'text-canvas-soft' : 'text-body';

    return (
        <div className={className}>
            <span className="inline-flex items-center gap-sm">
                <BrandMark />
                <span className={`text-display-sm font-black tracking-tight ${word}`}>
                    Quantah
                </span>
            </span>
            {tagline && (
                <p className={`mt-sm text-body-md ${tag}`}>{t('Every receipt counts.')}</p>
            )}
        </div>
    );
}
