import Band from '@/Components/Band';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head } from '@inertiajs/react';

/**
 * Privacidade — Política de Privacidade (LGPD), página pública de texto (STORY-026). Destino do
 * aviso de consentimento do formulário de lead B2B. Conteúdo base validado pelo PO; só tokens do
 * DS, pt-BR. Na casca pública (face b2c, a face padrão do app).
 */

// Seções da política (conteúdo base LGPD — spec §5). Validado pelo PO.
const SECOES = [
    {
        t: 'Quem é o controlador',
        c: 'A Quantah é a controladora dos dados pessoais tratados nesta plataforma. Para dúvidas sobre privacidade, fale com nossa equipe pelo canal de contato ao final desta página.',
    },
    {
        t: 'Que dados coletamos',
        c: 'Na captação de interessados no Quantah Intelligence, coletamos nome, e-mail e empresa informados no formulário. No uso do app do Coletador, tratamos os dados necessários à coleta de cupons fiscais, conforme descrito no próprio fluxo.',
    },
    {
        t: 'Para que usamos',
        c: 'Os dados do formulário B2B são usados exclusivamente para entrar em contato sobre o Quantah Intelligence. Não usamos esses dados para outras finalidades sem informar você.',
    },
    {
        t: 'Base legal',
        c: 'O tratamento dos dados de contato B2B se dá com base no seu consentimento, manifestado ao enviar o formulário. Você pode retirar o consentimento a qualquer momento pelos canais abaixo.',
    },
    {
        t: 'Compartilhamento',
        c: 'Não vendemos seus dados pessoais. Eventuais operadores que nos apoiam (ex.: hospedagem) tratam os dados apenas sob nossa instrução e com proteção adequada.',
    },
    {
        t: 'Seus direitos',
        c: 'Você pode solicitar acesso, correção ou exclusão dos seus dados, além de informações sobre o tratamento. Atendemos às solicitações previstas na LGPD.',
    },
    {
        t: 'Retenção e contato',
        c: 'Mantemos os dados de contato pelo tempo necessário à finalidade informada. Para exercer seus direitos ou tirar dúvidas, fale com a Quantah pelo e-mail de contato divulgado nos nossos canais oficiais.',
    },
];

export default function Privacidade() {
    return (
        <PublicLayout face="b2c">
            <Head title="Política de Privacidade — Quantah" />

            <Band variant="content" aria-labelledby="privacidade-h1">
                <div className="mx-auto flex max-w-2xl flex-col gap-xl">
                    <div>
                        <h1 id="privacidade-h1" className="font-display text-display-sm font-black text-ink lg:text-display-md">
                            Política de Privacidade
                        </h1>
                        <p className="mt-sm text-body-sm text-mute">Como a Quantah trata seus dados pessoais.</p>
                    </div>

                    {SECOES.map((secao) => (
                        <section key={secao.t}>
                            <h2 className="text-body-lg font-semibold text-ink">{secao.t}</h2>
                            <p className="mt-xs text-body-md text-body">{secao.c}</p>
                        </section>
                    ))}
                </div>
            </Band>
        </PublicLayout>
    );
}
