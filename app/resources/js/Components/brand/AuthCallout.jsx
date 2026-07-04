import { AlertIcon, CheckIcon } from '@/Components/icons';

/**
 * AuthCallout — bloco de mensagem das telas de acesso (padrão `pattern.error`/status).
 * Feedback nunca só por cor: ícone + texto. `error` usa `role="alert"` e é anunciado ao
 * leitor de tela; `ok` usa `role="status"`.
 */
const VARIANTS = {
    ok: { cls: 'bg-primary-pale text-positive-deep', Icon: CheckIcon, role: 'status' },
    error: {
        cls: 'border border-negative text-negative-darkest',
        Icon: AlertIcon,
        role: 'alert',
    },
};

export default function AuthCallout({ variant = 'ok', className = '', children, ...props }) {
    const { cls, Icon, role } = VARIANTS[variant] ?? VARIANTS.ok;

    return (
        <div
            role={role}
            className={`flex items-start gap-sm rounded-lg px-lg py-md text-body-sm font-semibold ${cls} ${className}`}
            {...props}
        >
            <Icon className="mt-xxs h-lg w-lg shrink-0" />
            <span>{children}</span>
        </div>
    );
}
