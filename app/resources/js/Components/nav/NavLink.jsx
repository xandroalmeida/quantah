/**
 * NavLink (nav.link do DS, STORY-006). Texto `body-sm-strong`. Item **ativo** ganha
 * indicador `primary` (borda inferior verde); inativo é `body` e escurece no hover.
 * Foco visível por teclado (ring `ink`). Renderiza um `<a>` — passe `href`.
 */
export default function NavLink({ active = false, className = '', children, ...props }) {
    const state = active
        ? 'border-primary text-ink'
        : 'border-transparent text-body hover:text-ink';

    return (
        <a
            aria-current={active ? 'page' : undefined}
            className={`inline-flex min-h-3xl items-center border-b-2 px-xs text-body-sm font-semibold transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-ink focus:ring-offset-2 focus:ring-offset-canvas ${state} ${className}`}
            {...props}
        >
            {children}
        </a>
    );
}
