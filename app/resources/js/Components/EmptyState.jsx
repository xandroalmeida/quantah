import Button from '@/Components/Button';

/**
 * EmptyState do DS (STORY-006). Ícone leve + título `display-xs` + instrução `body-md`
 * + `button.primary`. **Sempre instrui o próximo passo** — "Sem dados" sozinho é
 * proibido (components.md). Ex.: "Você ainda não enviou cupons. Enviar o primeiro."
 */
export default function EmptyState({
    icon,
    title,
    children,
    actionLabel,
    onAction,
    actionProps,
    className = '',
    ...props
}) {
    return (
        <div
            className={`flex flex-col items-center gap-lg rounded-xl bg-canvas p-2xl text-center ${className}`}
            {...props}
        >
            {icon && (
                <span className="text-mute" aria-hidden="true">
                    {icon}
                </span>
            )}
            <h3 className="text-display-xs text-ink">{title}</h3>
            {children && <p className="max-w-md text-body-md text-body">{children}</p>}
            {actionLabel && (
                <Button variant="primary" onClick={onAction} {...actionProps}>
                    {actionLabel}
                </Button>
            )}
        </div>
    );
}
