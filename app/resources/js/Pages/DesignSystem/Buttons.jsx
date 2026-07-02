import { Head } from '@inertiajs/react';
import { useState } from 'react';
import Button from '@/Components/Button';

/**
 * Vitrine dos botões do DS (STORY-004 / CA-5). Base plugável da vitrine completa
 * (kitchen sink) que é finalizada na STORY-006. O contador prova onClick vs.
 * disabled/loading (que não disparam).
 */
export default function Buttons() {
    const [count, setCount] = useState(0);
    const inc = () => setCount((c) => c + 1);

    return (
        <>
            <Head title="DS — Botões" />
            <main className="min-h-screen bg-canvas-soft px-lg py-3xl text-ink">
                <div className="mx-auto flex max-w-4xl flex-col gap-2xl">
                    <header className="flex flex-col gap-sm">
                        <h1
                            data-testid="showcase-title"
                            className="font-display text-display-md font-black"
                        >
                            Botões do Design System
                        </h1>
                        <p className="text-body-md text-body">
                            Cliques em botões habilitados contam:{' '}
                            <span data-testid="click-count" className="font-semibold text-ink">
                                {count}
                            </span>
                        </p>
                    </header>

                    <section className="flex flex-col gap-lg">
                        <h2 className="text-display-xs">Variantes</h2>
                        <div className="flex flex-wrap items-center gap-lg">
                            <Button variant="primary" data-testid="btn-primary" onClick={inc}>
                                Enviar cupom
                            </Button>
                            <Button variant="secondary" data-testid="btn-secondary" onClick={inc}>
                                Cancelar
                            </Button>
                            <Button variant="tertiary" data-testid="btn-tertiary" onClick={inc}>
                                Ver detalhes
                            </Button>
                            <Button variant="danger" data-testid="btn-danger" onClick={inc}>
                                Remover conta
                            </Button>
                            <Button
                                variant="icon"
                                data-testid="btn-icon"
                                aria-label="Adicionar cupom"
                                onClick={inc}
                            >
                                <PlusIcon />
                            </Button>
                        </div>
                    </section>

                    <section className="flex flex-col gap-lg">
                        <h2 className="text-display-xs">Estados</h2>
                        <div className="flex flex-wrap items-center gap-lg">
                            <Button
                                variant="primary"
                                data-testid="btn-disabled"
                                disabled
                                onClick={inc}
                            >
                                Desabilitado
                            </Button>
                            <Button
                                variant="primary"
                                data-testid="btn-loading"
                                loading
                                onClick={inc}
                            >
                                Salvando…
                            </Button>
                        </div>
                        <p className="text-body-sm text-mute">
                            Hover, foco (Tab) e pressed são visíveis nos botões habilitados acima.
                        </p>
                    </section>
                </div>
            </main>
        </>
    );
}

function PlusIcon() {
    return (
        <svg className="h-lg w-lg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M12 5v14M5 12h14"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
            />
        </svg>
    );
}
