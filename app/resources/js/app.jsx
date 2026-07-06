import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import VersionStamp from './Components/VersionStamp';
import { setTranslations } from './i18n';
import { startVersionWatcher } from './versionWatcher';
import { initInstallCapture } from './pwa/installPrompt';
import { registerServiceWorker } from './pwa/registerServiceWorker';

const appName = import.meta.env.VITE_APP_NAME || 'Quantah';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        // Captura do convite de instalação da PWA (beforeinstallprompt) — iniciada ANTES do
        // primeiro render para não perder o evento se o browser disparar cedo (STORY-038).
        initInstallCapture();

        // Registra o dicionário do locale ativo antes do primeiro render (ADR-011). Monolíngue:
        // o mapa é o mesmo em toda navegação, então basta uma vez a partir da página inicial.
        setTranslations(props.initialPage.props.translations);

        const root = createRoot(el);

        // Versão/asset CRAVADOS no HTML pelo servidor (meta tags do Blade) — fonte de
        // verdade para o carimbo e o boot do watcher, garantindo que refletem o HTML que
        // de fato carregou (não um prop que se possa duvidar). Cai para o prop do Inertia
        // se o meta faltar (ex.: dev sem esse HTML).
        const meta = (nome) => document.querySelector(`meta[name="${nome}"]`)?.content || undefined;
        const version = meta('app-version') ?? props.initialPage.props.version;
        const assetVersion = meta('app-asset') ?? props.initialPage.props.assetVersion;

        root.render(
            <>
                <App {...props} />
                <VersionStamp version={version} />
            </>,
        );

        // Auto-atualização: recarrega quando o servidor sobe um deploy novo, priorizando o
        // retorno ao primeiro plano no PWA mobile. Vigia a MESMA tag do deploy (`version`),
        // com o hash do bundle (`assetVersion`) como rede de segurança.
        startVersionWatcher({ version, asset: assetVersion });

        // Registra o service worker mínimo (public/sw.js) — habilita a PWA instalável sem
        // cache; o versionWatcher acima segue como único mecanismo de atualização (IDR-016).
        registerServiceWorker();
    },
    progress: {
        color: '#4B5563',
    },
});
