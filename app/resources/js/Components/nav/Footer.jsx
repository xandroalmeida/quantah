/**
 * Footer do DS (STORY-006). Band escuro: fundo `ink`, texto `canvas-soft`, `body-sm`,
 * padding `3xl xl` (components.md › Navegação › footer).
 */
export default function Footer({ className = '', children, ...props }) {
    return (
        <footer
            className={`bg-ink px-xl py-3xl text-body-sm text-canvas-soft ${className}`}
            {...props}
        >
            {children}
        </footer>
    );
}
