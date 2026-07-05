import Band from '@/Components/Band';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import TextField from '@/Components/inputs/TextField';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link, useForm } from '@inertiajs/react';

/**
 * LandingB2B — vitrine pública do Quantah Intelligence + captação de lead (STORY-026 ·
 * SCREEN-STORY-026). "Do cupom ao insight." Casca pública face b2b (DDR-005, sem login),
 * tom sério/analítico, só tokens do DS, pt-BR. Sucesso via PRG → /intelligence/obrigado
 * (DDR-006). Substitui a antiga página "reservada" nesta rota.
 */

// O que é / para quem — blocos sóbrios (spec §5).
const BLOCOS = [
    { t: 'Preço real, item a item', b: 'O que foi de fato pago no varejo, por região e período — direto da nota fiscal.' },
    { t: 'Sortimento e share of shelf', b: 'Que produtos aparecem, onde e com que frequência na cesta do consumidor.' },
    { t: 'Para quem decide com dados', b: 'Indústria/CPG, varejo e times de dados que precisam de preço e sortimento reais.' },
];

export default function LandingB2B() {
    const { data, setData, post, processing, errors } = useForm({ nome: '', email: '', empresa: '' });

    function submit(e) {
        e.preventDefault();
        post('/intelligence/leads');
    }

    return (
        <PublicLayout face="b2b">
            <Head title="Quantah Intelligence — Do cupom ao insight." />

            {/* HERO — hero-band-dark */}
            <Band variant="hero-dark" aria-labelledby="b2b-h1">
                <h1 id="b2b-h1" className="font-display text-display-md font-black text-primary lg:text-display-xl">
                    Do cupom ao insight.
                </h1>
                <p className="mt-lg max-w-2xl text-body-lg text-canvas-soft">
                    Preço praticado, share of shelf e índices de inflação por região, a partir de milhões de
                    cupons fiscais. Para indústria, varejo e times de dados.
                </p>
            </Band>

            {/* O QUE É / PARA QUEM — content-band */}
            <Band variant="content" aria-label="O que é o Quantah Intelligence">
                <div className="grid gap-xl lg:grid-cols-3">
                    {BLOCOS.map((bloco) => (
                        <div key={bloco.t}>
                            <h2 className="text-body-lg font-semibold text-ink">{bloco.t}</h2>
                            <p className="mt-xs text-body-md text-body">{bloco.b}</p>
                        </div>
                    ))}
                </div>
            </Band>

            {/* FORMULÁRIO — pattern.form */}
            <Band variant="hero" aria-labelledby="form-h2" className="lg:grid lg:grid-cols-2 lg:items-center lg:gap-3xl">
                <div className="hidden lg:block">
                    <p className="font-display text-display-sm font-black text-ink">Fale com o time do Intelligence.</p>
                    <p className="mt-md max-w-md text-body-md text-body">
                        Deixe seu contato e mostramos como transformar cupons fiscais em decisão de preço e
                        sortimento.
                    </p>
                </div>

                <Card variant="content" className="mt-xl flex w-full max-w-md flex-col gap-lg lg:mt-0">
                    <form onSubmit={submit} className="flex flex-col gap-lg" noValidate>
                        <div>
                            <h2 id="form-h2" className="font-display text-display-xs font-black text-ink">
                                Quero saber mais
                            </h2>
                            <p className="mt-xs text-body-sm text-body">
                                Nome, e-mail e empresa — nossa equipe entra em contato.
                            </p>
                        </div>

                        <TextField
                            label="Nome"
                            value={data.nome}
                            onChange={(e) => setData('nome', e.target.value)}
                            error={errors.nome}
                            errorTestId="landing-b2b-error-nome"
                            data-testid="landing-b2b-field-nome"
                            autoComplete="name"
                        />
                        <TextField
                            label="E-mail"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            error={errors.email}
                            errorTestId="landing-b2b-error-email"
                            data-testid="landing-b2b-field-email"
                            autoComplete="email"
                        />
                        <TextField
                            label="Empresa"
                            value={data.empresa}
                            onChange={(e) => setData('empresa', e.target.value)}
                            error={errors.empresa}
                            errorTestId="landing-b2b-error-empresa"
                            data-testid="landing-b2b-field-empresa"
                            autoComplete="organization"
                        />

                        <p className="text-caption text-mute">
                            Ao enviar, você concorda que a Quantah use seu nome, e-mail e empresa para entrar
                            em contato sobre o Quantah Intelligence. Saiba como tratamos seus dados na{' '}
                            <Link href="/privacidade" className="text-body underline">
                                Política de Privacidade
                            </Link>
                            .
                        </p>

                        <Button type="submit" variant="primary" loading={processing} data-testid="landing-b2b-cta-enviar">
                            Quero saber mais
                        </Button>
                    </form>
                </Card>
            </Band>
        </PublicLayout>
    );
}
