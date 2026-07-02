/**
 * Button — componente do Design System Quantah (STORY-004).
 *
 * 5 variantes (primary/secondary/tertiary/danger/icon), estados
 * default/hover/focus/pressed/disabled/loading — todos derivados de tokens do DS
 * (ver docs/project-state/design/system/components.md › Botões e tokens.md).
 * Regra de ouro: `primary` (verde) é o ÚNICO CTA por contexto.
 *
 * A11y: alvo de toque ≥48px (min-h/min-w-3xl), foco visível por teclado
 * (focus-visible ring), texto sempre via token de contraste AA. `loading`
 * bloqueia o clique (disabled nativo) e expõe `aria-busy`; `variant="icon"`
 * exige `aria-label` (verbo + objeto) — passado pelo consumidor.
 */

// Forma: raio, padding e alvo de toque. Botão é rounded-xl (24px, assinatura);
// icon é circular (rounded-full) 48×48.
const SHAPE = {
    text: 'rounded-xl px-xl py-md min-h-3xl',
    icon: 'rounded-full p-sm min-h-3xl min-w-3xl',
};

// Cor por variante + estados hover/pressed. Só tokens do DS — zero valor cru.
const VARIANTS = {
    primary: 'bg-primary text-on-primary hover:bg-primary-active active:bg-primary-neutral',
    secondary: 'bg-canvas-soft text-ink hover:bg-primary-pale active:bg-primary-neutral',
    tertiary: 'bg-canvas text-ink border border-ink hover:bg-canvas-soft active:bg-primary-pale',
    danger: 'bg-negative text-canvas hover:bg-negative-deep active:bg-negative-darkest',
    icon: 'bg-canvas text-ink border border-ink hover:bg-canvas-soft active:bg-primary-pale',
};

const BASE =
    'relative inline-flex items-center justify-center gap-sm text-button-md font-semibold ' +
    'transition-colors duration-fast focus-visible:outline-none focus-visible:ring-2 ' +
    'focus-visible:ring-ink focus-visible:ring-offset-2 focus-visible:ring-offset-canvas-soft ' +
    'disabled:cursor-not-allowed';

export default function Button({
    variant = 'primary',
    type = 'button',
    loading = false,
    disabled = false,
    className = '',
    children,
    ...props
}) {
    const shape = variant === 'icon' ? SHAPE.icon : SHAPE.text;
    const colors = VARIANTS[variant] ?? VARIANTS.primary;
    // disabled dimma (38%); loading não dimma — mostra o spinner sobre o rótulo.
    const dim = disabled && !loading ? 'opacity-[0.38]' : '';

    return (
        <button
            type={type}
            disabled={disabled || loading}
            aria-busy={loading || undefined}
            className={`${BASE} ${shape} ${colors} ${dim} ${className}`}
            {...props}
        >
            {loading && (
                <span
                    data-testid="spinner"
                    role="status"
                    aria-hidden="true"
                    className="absolute inline-flex"
                >
                    <svg className="h-lg w-lg animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle
                            className="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            strokeWidth="4"
                        />
                        <path
                            className="opacity-90"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                        />
                    </svg>
                </span>
            )}
            <span className={loading ? 'invisible' : 'inline-flex items-center gap-sm'}>
                {children}
            </span>
        </button>
    );
}
