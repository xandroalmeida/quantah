import { t } from '@/i18n';
import BrandMark from './BrandMark';

/**
 * BrandLockup — `brand.lockup` do DS (DDR-004): BrandMark + wordmark "Quantah" (Inter 900),
 * com tagline opcional. É o único lugar onde a marca aparece em escala numa tela.
 *
 * `onDark` inverte o wordmark/tagline para uso sobre superfície escura (painel de hero,
 * `card.feature-dark`). "Quantah" é nome próprio — não traduzido.
 *
 * `compact` reduz o wordmark no mobile (`display-xs` → `display-sm` a partir de `md`), para o
 * header público caber sem cortar o CTA "Entrar" em 360–390px (STORY-033). Sem `compact`, mantém
 * a escala cheia (`display-sm`) usada no hero.
 */
export default function BrandLockup({ tagline = false, onDark = false, compact = false, className = '' }) {
    const word = onDark ? 'text-canvas' : 'text-ink';
    const tag = onDark ? 'text-canvas-soft' : 'text-body';
    const wordSize = compact ? 'text-display-xs md:text-display-sm' : 'text-display-sm';

    return (
        <div className={className}>
            <span className="inline-flex items-center gap-sm">
                <BrandMark />
                <span className={`${wordSize} font-black tracking-tight ${word}`}>
                    Quantah
                </span>
            </span>
            {tagline && (
                <p className={`mt-sm text-body-md ${tag}`}>{t('Every receipt counts.')}</p>
            )}
        </div>
    );
}
