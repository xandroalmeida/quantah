import BrandLockup from '@/Components/brand/BrandLockup';
import { Head } from '@inertiajs/react';

/**
 * Área B2B — Quantah Intelligence (STORY-023 · ADR-010 §3). Nesta onda a área é apenas
 * **reservada**: pública, sem login e sem features. Reserva o namespace `/intelligence`
 * para a captação de lead do EPIC-005, sem retrabalho estrutural. Não há CTA de acesso
 * (não existe login B2B — PDR-003); a página comunica o valor e o "em breve".
 */
export default function Reservado() {
    return (
        <>
            <Head title="Quantah Intelligence" />
            <main
                data-testid="b2b-intelligence"
                className="flex min-h-screen flex-col items-center justify-center gap-2xl bg-canvas-soft px-lg py-3xl text-center text-ink"
            >
                <section className="flex w-full max-w-xl flex-col items-center gap-lg rounded-xl bg-canvas p-2xl shadow-elev-2">
                    <BrandLockup className="flex flex-col items-center gap-sm" />

                    <p
                        data-testid="b2b-eyebrow"
                        className="text-body-sm font-semibold uppercase tracking-widest text-mute"
                    >
                        Quantah Intelligence
                    </p>

                    <h1 className="font-display text-display-md font-black text-ink lg:text-display-lg">
                        Inteligência de preços do varejo
                    </h1>

                    <p className="text-body-lg text-body">
                        Preço praticado, share of shelf e índices de inflação por região, a partir
                        de milhões de cupons fiscais. Para indústria, varejo e times de dados.
                    </p>

                    {/* Reserva do lugar da captação de lead — materializada no EPIC-005. Sem
                        formulário nem login nesta onda (área apenas reservada). */}
                    <span
                        data-testid="b2b-em-breve"
                        className="rounded-pill border border-mute bg-canvas px-md py-xs text-body-sm font-semibold text-body"
                    >
                        Em breve
                    </span>
                </section>
            </main>
        </>
    );
}
