import { Link } from '@inertiajs/react';
import BrandLockup from '@/Components/brand/BrandLockup';
import CtaLink from '@/Components/CtaLink';
import Footer from '@/Components/nav/Footer';
import NavBar from '@/Components/nav/NavBar';
import NavLink from '@/Components/nav/NavLink';

/**
 * PublicLayout — casca das superfícies públicas (DDR-005 · pattern.public-shell). Header
 * (`nav.bar`) + `<main>` + `footer` comuns às landings B2C e B2B, com header/rodapé contextuais
 * por `face`. Só compõe componentes do DS (`nav.bar`, `nav.link`, `footer`, `brand.lockup`) —
 * sem primitivo novo.
 *
 * Regra de ouro do DS: no máximo um `button.primary` verde por contexto — só a face B2C tem o CTA
 * "Entrar" (não há login B2B nesta onda — PDR-003).
 */
const FACES = {
    b2c: {
        home: '/',
        cross: { label: 'Para empresas', href: '/intelligence', navTestid: 'landing-b2c-nav-b2b', footTestid: 'landing-b2c-foot-b2b' },
        cta: { label: 'Entrar', href: '/login', testid: 'landing-b2c-nav-entrar' },
    },
    b2b: {
        home: '/intelligence',
        cross: { label: 'Voltar ao app', href: '/', navTestid: 'landing-b2b-nav-b2c', footTestid: 'landing-b2b-foot-b2c' },
        cta: null,
    },
};

export default function PublicLayout({ face = 'b2c', children }) {
    const cfg = FACES[face] ?? FACES.b2c;

    return (
        <div className="flex min-h-screen flex-col bg-canvas-soft">
            <NavBar aria-label="Navegação principal" data-testid="public-nav">
                <Link href={cfg.home} aria-label="Quantah — página inicial" className="rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-ink">
                    <BrandLockup />
                </Link>
                <div className="ml-auto flex items-center gap-lg">
                    <NavLink href={cfg.cross.href} data-testid={cfg.cross.navTestid}>
                        {cfg.cross.label}
                    </NavLink>
                    {cfg.cta && (
                        <CtaLink variant="primary" href={cfg.cta.href} data-testid={cfg.cta.testid}>
                            {cfg.cta.label}
                        </CtaLink>
                    )}
                </div>
            </NavBar>

            <main className="flex-1">{children}</main>

            <Footer data-testid="public-footer">
                <div className="mx-auto flex max-w-5xl flex-col gap-lg lg:flex-row lg:items-center lg:justify-between">
                    <span className="font-display text-display-xs font-black text-canvas">Quantah</span>
                    <nav aria-label="Rodapé" className="flex flex-wrap items-center gap-lg">
                        <Link
                            href={cfg.cross.href}
                            data-testid={cfg.cross.footTestid}
                            className="border-b-2 border-transparent pb-xxs font-semibold text-canvas-soft transition-colors duration-fast hover:text-canvas focus:outline-none focus-visible:border-primary"
                        >
                            {cfg.cross.label}
                        </Link>
                    </nav>
                    <span className="text-caption text-mute">© 2026 Quantah · Cada nota conta.</span>
                </div>
            </Footer>
        </div>
    );
}
