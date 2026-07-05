import BrandMark from '@/Components/brand/BrandMark';
import Band from '@/Components/Band';
import CtaLink from '@/Components/CtaLink';
import { t } from '@/i18n';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head } from '@inertiajs/react';

/**
 * LandingB2C — porta de entrada pública do Coletador (STORY-025 · SCREEN-STORY-025-landing-b2c).
 * "Cada nota conta." Casca compartilhada (DDR-005), herança de marca do acesso (DDR-004), só
 * tokens do DS, verde como único accent de CTA, pt-BR. Rota pública `/` (home).
 */

// Como funciona — 3 passos (design-handoff decisão #2; voz B2C próxima/encorajadora).
const PASSOS = [
    { n: '1', titulo: 'Escaneie a nota', corpo: 'Aponte a câmera para o QR Code do cupom fiscal da sua compra.' },
    { n: '2', titulo: 'Acumule cashback', corpo: 'Cada nota validada vira saldo em reais na sua carteira.' },
    { n: '3', titulo: 'Saque o saldo', corpo: 'Quando quiser, saque por PIX direto para a sua conta.' },
];

export default function LandingB2C() {
    return (
        <PublicLayout face="b2c">
            <Head title="Quantah — Cada nota conta." />

            {/* HERO — hero-band (sage) */}
            <Band
                variant="hero"
                data-testid="landing-b2c-hero"
                aria-labelledby="hero-h1"
                className="lg:grid lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:gap-3xl"
            >
                <div>
                    <h1 id="hero-h1" className="font-display text-display-md font-black text-ink lg:text-display-xl">
                        {t('Every receipt counts.')}
                    </h1>
                    <p className="mt-lg max-w-xl text-body-lg text-body">
                        Transforme as notas fiscais das suas compras em dinheiro de volta. Escaneie o
                        cupom, junte cashback e saque quando quiser.
                    </p>
                    <div className="mt-xl flex flex-col gap-md lg:flex-row lg:items-center">
                        <CtaLink variant="primary" href="/login" data-testid="landing-b2c-cta-entrar">
                            Entrar / Criar conta
                        </CtaLink>
                        <CtaLink variant="secondary" href="/intelligence" data-testid="landing-b2c-cta-b2b">
                            Para empresas →
                        </CtaLink>
                    </div>
                </div>

                {/* Painel de marca (desktop) — card.feature-dark: momento de marca que comunica o resultado. */}
                <aside
                    aria-hidden="true"
                    className="mt-2xl hidden flex-col gap-lg rounded-xl bg-ink p-2xl lg:mt-0 lg:flex"
                >
                    <p className="font-display text-display-md font-black text-primary">Cada nota vira saldo.</p>
                    <div className="flex items-center gap-md">
                        <BrandMark />
                        <div>
                            <p className="text-display-xs font-black text-canvas">+ R$ 0,03</p>
                            <p className="text-body-sm text-canvas-soft">creditados nesta nota</p>
                        </div>
                    </div>
                </aside>
            </Band>

            {/* COMO FUNCIONA — content-band (branco) */}
            <Band variant="content" data-testid="landing-b2c-como-funciona" aria-labelledby="como-h2">
                <h2 id="como-h2" className="font-display text-display-sm font-black text-ink lg:text-display-md">
                    Como funciona
                </h2>
                <ol className="mt-xl grid gap-xl lg:grid-cols-3">
                    {PASSOS.map((passo) => (
                        <li key={passo.n} className="flex gap-md lg:flex-col">
                            <span
                                aria-hidden="true"
                                className="inline-flex h-3xl w-3xl flex-none items-center justify-center rounded-xl bg-primary-pale font-display text-body-lg font-black text-ink-deep"
                            >
                                {passo.n}
                            </span>
                            <div>
                                <h3 className="text-body-lg font-semibold text-ink">{passo.titulo}</h3>
                                <p className="mt-xs text-body-md text-body">{passo.corpo}</p>
                            </div>
                        </li>
                    ))}
                </ol>
            </Band>

            {/* FECHO — hero-band-dark (verde sobre ink) */}
            <Band variant="hero-dark" className="flex flex-col items-center gap-lg text-center">
                <p className="font-display text-display-md font-black text-primary">{t('Every receipt counts.')}</p>
                <p className="max-w-xl text-body-md text-canvas-soft">
                    Comece agora a transformar seus cupons em cashback.
                </p>
                <CtaLink variant="primary" href="/login" data-testid="landing-b2c-cta-entrar-fim">
                    Entrar / Criar conta
                </CtaLink>
            </Band>
        </PublicLayout>
    );
}
