import { Head } from '@inertiajs/react';

export default function Hello({ appName, environment, laravelVersion, phpVersion }) {
    return (
        <>
            <Head title="Hello" />
            <main
                data-testid="hello-world"
                className="flex min-h-screen flex-col items-center justify-center gap-2xl bg-canvas-soft px-lg py-3xl text-center text-ink"
            >
                {/* pattern.surface-rhythm: página sage → card branco (elevação por contraste) */}
                <section className="flex w-full max-w-xl flex-col items-center gap-lg rounded-xl bg-canvas p-2xl shadow-elev-2">
                    <p
                        data-testid="hello-eyebrow"
                        className="text-body-sm font-semibold uppercase tracking-widest text-mute"
                    >
                        {appName} · inteligência de preços via NFC-e
                    </p>

                    <h1
                        data-testid="hello-title"
                        className="font-display text-display-md font-black text-ink lg:text-display-xl"
                    >
                        Olá do {appName} 👋
                    </h1>

                    <p data-testid="hello-body" className="text-body-lg text-body">
                        Hello world do {appName} no ar, agora com o tema do Design System — verde
                        de marca, Inter 900 e o raio de 24px que assina os botões.
                    </p>

                    <button
                        type="button"
                        data-testid="hello-cta"
                        className="inline-flex min-h-3xl items-center justify-center rounded-xl bg-primary px-xl py-md text-button-md font-semibold text-on-primary transition-colors duration-fast hover:bg-primary-active focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2 focus-visible:ring-offset-canvas-soft"
                    >
                        Enviar cupom
                    </button>
                </section>

                <span
                    data-testid="hello-environment"
                    className="rounded-pill border border-mute bg-canvas px-md py-xs text-body-sm text-body"
                >
                    ambiente: {environment}
                </span>

                <footer className="text-caption text-mute">
                    Laravel {laravelVersion} · PHP {phpVersion}
                </footer>
            </main>
        </>
    );
}
