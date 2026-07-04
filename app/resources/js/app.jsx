import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { setTranslations } from './i18n';

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

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
