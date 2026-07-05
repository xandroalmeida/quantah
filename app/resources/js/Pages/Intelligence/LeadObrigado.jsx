import Band from '@/Components/Band';
import CtaLink from '@/Components/CtaLink';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

/**
 * LeadObrigado — tela dedicada de agradecimento do lead B2B (STORY-026 · DDR-006 ·
 * pattern.lead-confirmacao). Servida por PRG após a captação; a mesma tela responde a e-mail
 * novo e duplicado (não revela existência de terceiro — LGPD). Confirmação positiva sóbria
 * (usa `positive`, não o verde de CTA). Tom B2B. Move o foco para o título ao montar (a11y).
 */
export default function LeadObrigado() {
    const tituloRef = useRef(null);

    useEffect(() => {
        tituloRef.current?.focus();
    }, []);

    return (
        <PublicLayout face="b2b">
            <Head title="Recebemos seu contato — Quantah Intelligence" />

            <Band variant="content" aria-labelledby="obrigado-h1" data-testid="landing-b2b-sucesso">
                <div className="mx-auto flex max-w-xl flex-col items-center gap-lg py-2xl text-center">
                    <span
                        aria-hidden="true"
                        className="inline-flex h-3xl w-3xl items-center justify-center rounded-full bg-primary-pale text-positive-deep"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" className="h-xl w-xl">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </span>

                    <h1
                        id="obrigado-h1"
                        ref={tituloRef}
                        tabIndex={-1}
                        className="font-display text-display-sm font-black text-ink focus:outline-none"
                    >
                        Recebemos seu contato.
                    </h1>
                    <p className="max-w-md text-body-md text-body">
                        Nossa equipe do Quantah Intelligence entra em contato em breve pelo e-mail informado.
                    </p>

                    <CtaLink variant="secondary" href="/intelligence" data-testid="landing-b2b-sucesso-voltar">
                        Voltar ao Quantah Intelligence
                    </CtaLink>
                </div>
            </Band>
        </PublicLayout>
    );
}
