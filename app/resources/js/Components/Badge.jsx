import { AlertIcon, CheckIcon, InfoIcon, XIcon } from './icons';

/**
 * Badge — status pill do DS (STORY-006). `body-sm-strong`, raio `pill`, padding
 * `xs md`. **Feedback nunca só cor:** sempre ícone + texto. Variantes (components.md
 * › Feedback & status): `positive` (cupom aceito), `negative` (cupom recusado),
 * `warning`, `info`.
 */
const VARIANTS = {
    positive: { cls: 'bg-primary-pale text-positive-deep', Icon: CheckIcon },
    negative: { cls: 'bg-negative-bg text-canvas', Icon: XIcon },
    warning: { cls: 'bg-warning text-warning-content', Icon: AlertIcon },
    info: { cls: 'bg-canvas-soft text-ink', Icon: InfoIcon },
};

export default function Badge({ variant = 'positive', className = '', children, ...props }) {
    const { cls, Icon } = VARIANTS[variant] ?? VARIANTS.positive;

    return (
        <span
            className={`inline-flex items-center gap-xs rounded-pill px-md py-xxs text-body-sm font-semibold ${cls} ${className}`}
            {...props}
        >
            <Icon className="h-lg w-lg" />
            {children}
        </span>
    );
}
