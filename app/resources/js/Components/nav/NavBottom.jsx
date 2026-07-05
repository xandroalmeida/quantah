/**
 * NavBottom (nav.bottom do DS · app do Colaborador, STORY-006). Navegação inferior
 * mobile; item **ativo** com indicador `primary` (ícone verde). Alvos ≥48px
 * (`min-h-3xl`/`min-w-3xl`). Recebe `items: [{ label, icon, active, href }]`.
 *
 * `pb-[max(env(safe-area-inset-bottom),0.5rem)]`: em aparelhos com notch/barra de gestos a barra
 * ganha folga abaixo dos alvos (≥48px) sem ficar sob a área de sistema; o piso de 0.5rem garante
 * respiro mesmo quando o iOS reporta o inset como 0 (PWA standalone). Sombra sutil para cima
 * (shadow-elev) faz a barra fixa "flutuar" sobre o conteúdo que rola por baixo. (STORY-033)
 */
export default function NavBottom({ items = [], itemProps, className = '', ...props }) {
    return (
        <nav
            className={`flex items-stretch justify-around border-t border-ink bg-canvas pb-[max(env(safe-area-inset-bottom),0.5rem)] text-ink shadow-[0_-2px_12px_rgba(14,15,12,0.06)] ${className}`}
            {...props}
        >
            {items.map((item) => (
                <a
                    key={item.label}
                    href={item.href ?? '#'}
                    data-testid={item.testid}
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
