/**
 * AuthDivider — divisor "ou" entre o login social e o formulário de e-mail/senha
 * (padrão `pattern.auth`, DDR-004). Hairline por contraste de superfície (canvas-soft).
 */
export default function AuthDivider({ children }) {
    return (
        <div className="flex items-center gap-md text-body-sm text-mute">
            <span className="h-px flex-1 bg-canvas-soft" aria-hidden="true" />
            {children}
            <span className="h-px flex-1 bg-canvas-soft" aria-hidden="true" />
        </div>
    );
}
