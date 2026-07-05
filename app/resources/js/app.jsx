import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import VersionStamp from './Components/VersionStamp';
import { setTranslations } from './i18n';
import { startVersionWatcher } from './versionWatcher';

const appName = import.meta.env.VITE_APP_NAME || 'Quantah';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        // Registra o dicionário do locale ativo antes do primeiro render (ADR-011). Monolíngue:
        // o mapa é o mesmo em toda navegação, então basta uma vez a partir da página inicial.
        setTranslations(props.initialPage.props.translations);

        const root = createRoot(el);

        // Versão global (constante na sessão) — lida uma vez do prop compartilhado.
        const version = props.initialPage.props.version;

        root.render(
            <>
                <App {...props} />
                <VersionStamp version={version} />
            </>,
        );

        // Auto-atualização: recarrega quando o servidor sobe um bundle novo (deploy),
        // priorizando o retorno ao primeiro plano no PWA mobile.
        startVersionWatcher(props.initialPage.props.assetVersion);
    },
    progress: {
        color: '#4B5563',
    },
});
