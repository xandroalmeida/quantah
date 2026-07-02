import { Head } from '@inertiajs/react';

export default function Hello({ appName, environment, laravelVersion, phpVersion }) {
    return (
        <>
            <Head title="Hello" />
            <main
                data-testid="hello-world"
                className="flex min-h-screen flex-col items-center justify-center gap-6 bg-gray-50 px-6 text-center text-gray-900 dark:bg-black dark:text-gray-100"
            >
                <p
                    data-testid="hello-eyebrow"
                    className="text-sm font-medium uppercase tracking-widest text-gray-500"
                >
                    {appName} · inteligência de preços via NFC-e
                </p>

                <h1 className="text-4xl font-bold sm:text-6xl">
                    Olá do {appName} 👋
                </h1>

                <p className="max-w-xl text-lg text-gray-600 dark:text-gray-400">
                    Hello world do {appName} no ar. Este é o trilho de entrega — cada
                    merge sobe automaticamente para homologação.
                </p>

                <span
                    data-testid="hello-environment"
                    className="rounded-full border border-gray-300 px-4 py-1 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400"
                >
                    ambiente: {environment}
                </span>

                <footer className="mt-4 text-xs text-gray-400">
                    Laravel {laravelVersion} · PHP {phpVersion}
                </footer>
            </main>
        </>
    );
}
