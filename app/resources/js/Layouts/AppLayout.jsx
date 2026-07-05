import { HomeIcon, ReceiptIcon, UserIcon, WalletIcon } from '@/Components/icons';
import NavBar from '@/Components/nav/NavBar';
import NavBottom from '@/Components/nav/NavBottom';
import NavLink from '@/Components/nav/NavLink';
import { t } from '@/i18n';

/**
 * AppLayout — casca de navegação da área B2C autenticada (`pattern.app-shell`, DDR-007).
 *
 * Fonte única da navegação logada: `nav.bottom` (mobile) / `nav.bar` (desktop) persistente com as
 * 4 seções raiz (Início · Cupons · Carteira · Perfil). Item da seção atual ativo (`aria-current` +
 * indicador `primary`). "Início" volta à home-hub de qualquer tela (retorno consistente, sem beco).
 * Substitui o `SECOES` duplicado e o `AuthenticatedLayout` do Breeze (logo do Laravel) nas rotas logadas.
 *
 * Uso: `<AppLayout active="carteira"><Head .../> ...conteúdo... </AppLayout>`.
 * `active`: 'inicio' | 'cupons' | 'carteira' | 'perfil' (saque é sub-tela de Carteira → 'carteira').
 * Os identificadores de teste (`app-nav-*`) ficam na barra inferior (viewport mobile dos E2E).
 */
const SECOES = [
    { key: 'inicio', label: 'Home', href: '/inicio', icon: <HomeIcon /> },
    { key: 'cupons', label: 'Coupons', href: '/coletar', icon: <ReceiptIcon /> },
    { key: 'carteira', label: 'Wallet', href: '/carteira', icon: <WalletIcon /> },
    { key: 'perfil', label: 'Profile', href: '/profile', icon: <UserIcon /> },
];

export default function AppLayout({ active, children }) {
    const itens = SECOES.map((s) => ({
        label: t(s.label),
        href: s.href,
        icon: s.icon,
        active: s.key === active,
        testid: `app-nav-${s.key}`,
    }));

    return (
        <div className="flex h-screen flex-col bg-canvas-soft">
            <NavBar className="hidden lg:flex">
                <span className="mr-auto font-display text-body-lg font-black text-ink">
                    Quantah
                </span>
                {SECOES.map((s) => (
                    <NavLink key={s.key} href={s.href} active={s.key === active}>
                        {t(s.label)}
                    </NavLink>
                ))}
            </NavBar>

            <main className="flex-1 overflow-y-auto">{children}</main>

            <NavBottom className="shrink-0 lg:hidden" items={itens} data-testid="app-nav" />
        </div>
    );
}
