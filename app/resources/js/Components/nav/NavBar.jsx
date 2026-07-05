/**
 * NavBar (nav.bar do DS · topo web, STORY-006). Fundo `canvas`, texto `ink`, padding
 * `md xl`, sticky. Borda inferior hairline `ink` (elev.1). Recebe os `NavLink` como
 * children. Namespaced em `Components/nav/` para não colidir com o `NavLink` do Breeze.
 */
export default function NavBar({ className = '', children, ...props }) {
    return (
        <nav
            // overflow-x-auto: em telas estreitas os links rolam DENTRO da barra, sem
            // empurrar a largura da página (regra de ouro: body nunca rola na horizontal).
            className={`sticky top-0 z-20 flex items-center gap-sm overflow-x-auto border-b border-ink bg-canvas px-md py-md text-ink md:gap-lg md:px-xl ${className}`}
            {...props}
        >
            {children}
        </nav>
    );
}
