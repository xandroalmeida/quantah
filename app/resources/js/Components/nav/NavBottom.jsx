/**
 * NavBottom (nav.bottom do DS · app do Colaborador, STORY-006). Navegação inferior
 * mobile; item **ativo** com indicador `primary` (ícone verde). Alvos ≥48px
 * (`min-h-3xl`/`min-w-3xl`). Recebe `items: [{ label, icon, active, href }]`.
 */
export default function NavBottom({ items = [], itemProps, className = '', ...props }) {
    return (
        <nav
            className={`flex items-stretch justify-around border-t border-ink bg-canvas text-ink ${className}`}
            {...props}
        >
            {items.map((item) => (
                <a
                    key={item.label}
                    href={item.href ?? '#'}
                    aria-current={item.active ? 'page' : undefined}
                    className={`flex min-h-3xl min-w-3xl flex-1 flex-col items-center justify-center gap-xxs px-xs py-xs text-caption font-semibold focus:outline-none focus:ring-2 focus:ring-inset focus:ring-ink ${
                        item.active ? 'text-ink' : 'text-body'
                    }`}
                    {...itemProps}
                >
                    <span aria-hidden="true" className={item.active ? 'text-primary' : 'text-body'}>
                        {item.icon}
                    </span>
                    {item.label}
                </a>
            ))}
        </nav>
    );
}
