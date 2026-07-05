import { Link } from '@inertiajs/react';

/**
 * CtaLink — CTA de navegação com o visual dos botões do DS (button.primary / button.secondary),
 * renderizado como Inertia <Link> (navegação real, semântica de link) em vez de <button>. Para os
 * CTAs das superfícies públicas (landings). Não é componente novo do DS: é o `button.primary` /
 * `button.secondary` (components.md) aplicado a um link — mesmos tokens do `Button`.
 *
 * Regra de ouro do DS: `primary` (verde) em no máximo um CTA por contexto.
 * A11y: alvo ≥48px (min-h-3xl), foco visível por teclado (mesmo anel do Button).
 */
const SHAPE =
    'inline-flex items-center justify-center gap-sm rounded-xl px-md py-md min-h-3xl md:px-xl ' +
    'text-button-md font-semibold transition-colors duration-fast ' +
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink ' +
    'focus-visible:ring-offset-2 focus-visible:ring-offset-canvas-soft';

const VARIANTS = {
    primary: 'bg-primary text-on-primary hover:bg-primary-active active:bg-primary-neutral',
    secondary: 'bg-canvas-soft text-ink hover:bg-primary-pale active:bg-primary-neutral',
};

export default function CtaLink({ variant = 'primary', className = '', children, ...props }) {
    const colors = VARIANTS[variant] ?? VARIANTS.primary;

    return (
        <Link className={`${SHAPE} ${colors} ${className}`} {...props}>
            {children}
        </Link>
    );
}
