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
    // Rótulo diz o que faz — o destino é o escaneamento (/coletar), não uma lista (STORY-036).
    { key: 'cupons', label: 'Scan', href: '/coletar', icon: <ReceiptIcon /> },
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
        // Casca app-like. O documento rola (body scroll) e o nav.bottom é FIXO ao fundo do
        // viewport visível — não é irmão `shrink-0` de um `flex-1` como antes. No PWA standalone
        // do iOS, o layout antigo (`h-[100dvh]` + flex) empurrava o nav para fora quando o conteúdo
        // era mais alto (min-height:auto do flex) ou quando o iOS caía no fallback 100vh: os rótulos
        // ficavam cortados atrás da barra de gestos. Fixo + `safe-area-inset-bottom` é o padrão PWA
        // que nunca é cortado por altura de conteúdo nem por suporte a `dvh`. (STORY-033 · EPIC-007)
        // `pt-[safe-area-inset-top]` é 0 com status bar opaca (default), mas protege notch/landscape.
        <div className="min-h-screen min-h-[100dvh] bg-canvas-soft pt-[env(safe-area-inset-top)]">
            <NavBar className="sticky top-0 z-40 hidden lg:flex">
                <span className="mr-auto font-display text-body-lg font-black text-ink">
                    Quantah
                </span>
                {SECOES.map((s) => (
                    <NavLink key={s.key} href={s.href} active={s.key === active}>
                        {t(s.label)}
                    </NavLink>
                ))}
            </NavBar>

            {/* Folga inferior no mobile = altura do nav.bottom (~4.5rem) + safe-area, para o
                conteúdo nunca ficar encoberto pela barra fixa. No desktop o nav é no topo → sem folga. */}
            <main className="pb-[calc(4.5rem+env(safe-area-inset-bottom))] lg:pb-0">
                {children}
            </main>

            <NavBottom
                className="fixed inset-x-0 bottom-0 z-40 lg:hidden"
                items={itens}
                data-testid="app-nav"
            />
        </div>
    );
}
