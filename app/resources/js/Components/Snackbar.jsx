import { AlertIcon, CheckIcon, InfoIcon, XIcon } from './icons';

/**
 * Snackbar (toast) do DS (STORY-006). Fundo `canvas`, raio `xl`, padding `md lg`,
 * `body-sm`. Variantes success/warning/danger/info: **ícone + texto** (cor nunca
 * sozinha). Mudança dinâmica é anunciada via `aria-live="polite"` (a11y).
 */
const VARIANTS = {
    success: { Icon: CheckIcon, tint: 'text-positive' },
    warning: { Icon: AlertIcon, tint: 'text-warning-deep' },
    danger: { Icon: XIcon, tint: 'text-negative' },
    info: { Icon: InfoIcon, tint: 'text-ink' },
};

export default function Snackbar({ variant = 'info', className = '', children, ...props }) {
    const { Icon, tint } = VARIANTS[variant] ?? VARIANTS.info;

    return (
        <div
            role="status"
            aria-live="polite"
            className={`inline-flex items-center gap-md rounded-xl bg-canvas px-lg py-md text-body-sm text-ink shadow-elev-2 ${className}`}
            {...props}
        >
            <span className={tint}>
                <Icon className="h-lg w-lg" />
            </span>
            <span>{children}</span>
        </div>
    );
}
