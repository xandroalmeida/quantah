/**
 * GoogleButton — entrar/criar conta com Google (`brand.google-btn` do DS, DDR-004).
 *
 * A partir da STORY-022 é **funcional**: link real para o fluxo OAuth (`/auth/google/redirect`),
 * navegação de página inteira (sai do SPA — não é visita Inertia). Estilo neutro (outline
 * `button.tertiary`) com o logo oficial multicolor do Google (exceção ao DS de acento — marca de
 * terceiro, justificada no DDR-004). Nunca verde: o acento primário é do CTA de e-mail/senha.
 */
function GoogleG({ className = '' }) {
    return (
        <svg className={className} viewBox="0 0 48 48" aria-hidden="true">
            <path
                fill="#EA4335"
                d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.9 2.4 30.4 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.9 6.2C12.4 13.7 17.7 9.5 24 9.5z"
            />
            <path
                fill="#4285F4"
                d="M46.1 24.6c0-1.6-.1-3.1-.4-4.6H24v9.1h12.4c-.5 2.9-2.1 5.4-4.6 7l7.2 5.6c4.2-3.9 6.6-9.6 6.6-16.4z"
            />
            <path
                fill="#FBBC05"
                d="M10.5 28.4c-.5-1.5-.8-3.1-.8-4.7s.3-3.2.8-4.7l-7.9-6.2C.9 16.2 0 20 0 23.7s.9 7.5 2.6 10.9l7.9-6.2z"
            />
            <path
                fill="#34A853"
                d="M24 48c6.4 0 11.9-2.1 15.9-5.8l-7.2-5.6c-2 1.3-4.6 2.1-8.7 2.1-6.3 0-11.6-4.2-13.5-9.9l-7.9 6.2C6.5 42.6 14.6 48 24 48z"
            />
        </svg>
    );
}

export default function GoogleButton({ label, className = '' }) {
    return (
        <a
            href={route('google.redirect')}
            data-testid="acesso-google-btn"
            aria-label={label}
            className={
                'inline-flex min-h-3xl w-full items-center justify-center gap-sm rounded-xl ' +
                'border border-ink bg-canvas px-lg py-md text-button-md font-semibold text-ink ' +
                'transition-colors duration-fast hover:bg-canvas-soft ' +
                `focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink ${className}`
            }
        >
            <GoogleG className="h-lg w-lg shrink-0" />
            <span>{label}</span>
        </a>
    );
}
