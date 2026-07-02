import { Head } from '@inertiajs/react';
import { useState } from 'react';
import Button from '@/Components/Button';
import Card from '@/Components/Card';
import Badge from '@/Components/Badge';
import Snackbar from '@/Components/Snackbar';
import EmptyState from '@/Components/EmptyState';
import Skeleton from '@/Components/Skeleton';
import TextField from '@/Components/inputs/TextField';
import MaskedField from '@/Components/inputs/MaskedField';
import SelectField from '@/Components/inputs/SelectField';
import Switch from '@/Components/inputs/Switch';
import NavBar from '@/Components/nav/NavBar';
import NavLink from '@/Components/nav/NavLink';
import NavBottom from '@/Components/nav/NavBottom';
import Footer from '@/Components/nav/Footer';
import {
    HomeIcon,
    InboxIcon,
    ReceiptIcon,
    UserIcon,
    WalletIcon,
} from '@/Components/icons';

/**
 * Vitrine kitchen-sink do DS (STORY-006 · CA-2/CA-5). Página de referência em
 * homologação: renderiza TODOS os componentes do DS (botões STORY-004 + inputs
 * STORY-005 + superfície/feedback/nav desta estória) com estados, navegável por
 * âncoras via o próprio `NavBar`/`NavLink` (dogfooding). Ritmo de superfície: página
 * sage → clusters em card branco.
 */
export default function Showcase() {
    const [nfceKey, setNfceKey] = useState('');
    const [notify, setNotify] = useState(true);

    const bottomItems = [
        { label: 'Início', icon: <HomeIcon />, active: true, href: '#' },
        { label: 'Cupons', icon: <ReceiptIcon />, href: '#' },
        { label: 'Carteira', icon: <WalletIcon />, href: '#' },
        { label: 'Perfil', icon: <UserIcon />, href: '#' },
    ];

    return (
        <>
            <Head title="DS — Vitrine" />

            <NavBar data-testid="nav-bar">
                <span className="mr-auto font-display text-body-md font-black text-ink">Quantah DS</span>
                <NavLink href="#buttons">Botões</NavLink>
                <NavLink href="#inputs">Inputs</NavLink>
                <NavLink href="#cards" active data-testid="nav-link-cards">
                    Cards
                </NavLink>
                <NavLink href="#feedback">Feedback</NavLink>
                <NavLink href="#nav">Navegação</NavLink>
            </NavBar>

            <main className="bg-canvas-soft px-lg py-3xl text-ink">
                <div className="mx-auto flex max-w-3xl flex-col gap-3xl">
                    <header className="flex flex-col gap-sm">
                        <h1 data-testid="showcase-title" className="font-display text-display-md font-black">
                            Design System — Vitrine
                        </h1>
                        <p className="text-body-md text-body">
                            Todos os componentes do DS em código, com seus estados. Cada bloco referencia
                            sua spec em <code className="text-body-sm">design/system/components.md</code>.
                        </p>
                    </header>

                    {/* ── Botões (STORY-004 · components.md › Botões) ── */}
                    <Section id="buttons" title="Botões" spec="components.md › Botões">
                        <div className="flex flex-wrap items-center gap-lg">
                            <Button variant="primary" data-testid="showcase-button">
                                Enviar cupom
                            </Button>
                            <Button variant="secondary">Cancelar</Button>
                            <Button variant="tertiary">Ver detalhes</Button>
                            <Button variant="danger">Remover conta</Button>
                            <Button variant="primary" loading>
                                Salvando…
                            </Button>
                            <Button variant="primary" disabled>
                                Desabilitado
                            </Button>
                        </div>
                    </Section>

                    {/* ── Inputs (STORY-005 · components.md › Inputs) ── */}
                    <Section id="inputs" title="Inputs" spec="components.md › Inputs & formulários">
                        <div className="flex flex-col gap-xl">
                            <TextField
                                label="Nome completo"
                                hint="Como aparece no seu documento."
                                data-testid="showcase-text-input"
                            />
                            <MaskedField
                                label="Chave de acesso da NFC-e"
                                hint="44 dígitos. A máscara é só ajuda visual."
                                value={nfceKey}
                                onAccept={setNfceKey}
                            />
                            <SelectField label="UF" defaultValue="SP">
                                <option value="SP">São Paulo</option>
                                <option value="RJ">Rio de Janeiro</option>
                            </SelectField>
                            <Switch
                                label="Receber avisos de cashback"
                                checked={notify}
                                onChange={setNotify}
                            />
                            <TextField
                                label="E-mail"
                                error="Use um e-mail com @ e domínio."
                                defaultValue="quantah@"
                            />
                        </div>
                    </Section>

                    {/* ── Cards (components.md › Cards) — sobre a página sage ── */}
                    <Section id="cards" title="Cards" spec="components.md › Cards" plain>
                        <div className="grid gap-lg md:grid-cols-2">
                            <Card variant="content" data-testid="card-content" className="shadow-elev-2">
                                <h3 className="text-display-xs">card.content</h3>
                                <p className="mt-sm text-body-md text-body">
                                    Card branco padrão sobre canvas sage. Sem borda.
                                </p>
                            </Card>
                            <Card variant="feature-sage">
                                <h3 className="text-display-xs">card.feature-sage</h3>
                                <p className="mt-sm text-body-md text-body">Destaque sage.</p>
                            </Card>
                            <Card variant="feature-green">
                                <h3 className="text-display-xs">card.feature-green</h3>
                                <p className="mt-sm text-body-md text-body">Destaque verde suave.</p>
                            </Card>
                            <Card variant="feature-dark" data-testid="card-feature-dark">
                                <h3 className="text-display-xs text-primary">card.feature-dark</h3>
                                <p className="mt-sm text-body-md text-canvas-soft">
                                    Momento de marca — ink + verde. Uso pontual.
                                </p>
                            </Card>
                        </div>
                    </Section>

                    {/* ── Feedback & status (components.md › Feedback & status) ── */}
                    <Section id="feedback" title="Feedback & status" spec="components.md › Feedback & status">
                        <div className="flex flex-col gap-xl">
                            <div className="flex flex-wrap gap-md">
                                <Badge variant="positive" data-testid="badge-positive">
                                    Cupom aceito
                                </Badge>
                                <Badge variant="negative" data-testid="badge-negative">
                                    Cupom recusado
                                </Badge>
                                <Badge variant="warning">Em análise</Badge>
                                <Badge variant="info">Novo</Badge>
                            </div>

                            <div className="flex flex-col items-start gap-md">
                                <Snackbar variant="success" data-testid="snackbar-success">
                                    Cupom enviado com sucesso.
                                </Snackbar>
                                <Snackbar variant="warning">Conexão instável — tentando de novo.</Snackbar>
                                <Snackbar variant="danger">Não foi possível ler o QR.</Snackbar>
                                <Snackbar variant="info">Dica: aponte para o QR do rodapé.</Snackbar>
                            </div>

                            <EmptyState
                                data-testid="empty-state"
                                icon={<InboxIcon className="h-3xl w-3xl" />}
                                title="Você ainda não enviou cupons"
                                actionLabel="Enviar o primeiro"
                                actionProps={{ 'data-testid': 'empty-state-cta' }}
                            >
                                Escaneie o QR da sua NFC-e para começar a acumular cashback.
                            </EmptyState>

                            <div>
                                <p className="mb-sm text-body-sm font-semibold text-body">
                                    Skeleton (carregando)
                                </p>
                                <Card variant="content" className="flex flex-col gap-md shadow-elev-2">
                                    <div className="flex items-center gap-md">
                                        <Skeleton shape="circle" data-testid="skeleton" className="!h-2xl !w-2xl" />
                                        <Skeleton shape="line" className="max-w-xs" />
                                    </div>
                                    <Skeleton shape="block" />
                                </Card>
                            </div>
                        </div>
                    </Section>

                    {/* ── Navegação (components.md › Navegação) ── */}
                    <Section id="nav" title="Navegação" spec="components.md › Navegação">
                        <p className="text-body-md text-body">
                            A <code className="text-body-sm">NavBar</code> no topo desta página é o próprio
                            componente. Abaixo, a barra inferior do app (mobile):
                        </p>
                        <div className="mt-lg overflow-hidden rounded-xl border border-ink">
                            <NavBottom
                                data-testid="nav-bottom"
                                items={bottomItems}
                                itemProps={{ 'data-testid': 'nav-bottom-item' }}
                            />
                        </div>
                    </Section>
                </div>
            </main>

            <Footer data-testid="footer">
                <div className="mx-auto flex max-w-3xl flex-col gap-xs">
                    <span className="font-display font-black text-canvas">Quantah</span>
                    <span>Design System — vitrine de referência. EPIC-001.</span>
                </div>
            </Footer>
        </>
    );
}

/** Bloco de seção da vitrine: título + referência de spec + conteúdo (em card branco por padrão). */
function Section({ id, title, spec, plain = false, children }) {
    return (
        <section id={id} className="flex scroll-mt-3xl flex-col gap-lg">
            <div className="flex flex-col gap-xxs">
                <h2 className="text-display-xs">{title}</h2>
                <span className="text-body-sm text-mute">{spec}</span>
            </div>
            {plain ? (
                children
            ) : (
                <div className="rounded-xl bg-canvas p-xl shadow-elev-2">{children}</div>
            )}
        </section>
    );
}
