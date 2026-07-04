import BrandLockup from '@/Components/brand/BrandLockup';
import Card from '@/Components/Card';
import { t } from '@/i18n';
import { Link } from '@inertiajs/react';

/**
 * GuestLayout — casca das telas de acesso do Coletador (`pattern.auth`, DDR-004).
 * Substitui o scaffolding cinza do Breeze (com o logo do Laravel) pela identidade Quantah.
 *
 * - Mobile: página sage (`canvas-soft`) com o lockup + tagline sobre um card branco.
 * - Desktop (lg): split 50/50 — painel de marca escuro (`ink` + headline verde, momento de
 *   marca `card.feature-dark`) à esquerda e o mesmo card à direita, sem esticar.
 *
 * A ordem de leitura assistiva coloca o formulário (main) após o hero decorativo.
 */
const FOCUS_DARK =
    'rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-ink';
const FOCUS_LIGHT =
    'rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink';

export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen bg-canvas-soft lg:grid lg:grid-cols-2">
            <aside className="hidden bg-ink p-3xl lg:flex lg:flex-col">
                <Link href="/" className={FOCUS_DARK}>
                    <BrandLockup onDark />
                </Link>

                <div className="mt-auto">
                    <h2 className="text-display-xl font-black leading-none tracking-tight text-primary">
                        {t('Every receipt counts.')}
                    </h2>
                    <p className="mt-lg max-w-sm text-body-md text-canvas-soft">
                        {t(
                            'Turn the receipts from your purchases into money in your wallet — and help build the largest retail price database in Brazil.',
                        )}
                    </p>
                    <p className="mt-sm text-body-sm text-mute">
                        {t('From receipt to insight.')}
                    </p>
                </div>
            </aside>

            <main className="flex min-h-screen flex-col items-center justify-center px-lg py-2xl lg:px-3xl">
                <Link href="/" className={`mb-xl lg:hidden ${FOCUS_LIGHT}`}>
                    <BrandLockup tagline />
                </Link>

                <Card variant="content" className="w-full max-w-md shadow-elev-2">
                    {children}
                </Card>
            </main>
        </div>
    );
}
