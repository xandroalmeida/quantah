/**
 * Registro do service worker mínimo (public/sw.js) — o último passo para a área do Coletador
 * ser uma PWA instalável (STORY-038 · EPIC-008). A decisão de projeto (IDR-012) está no topo
 * de public/sw.js (IDR-016): SW sem cache; o `versionWatcher` segue como único mecanismo de atualização.
 *
 * Guardas (falha segura, silenciosa — registrar nunca pode quebrar o boot):
 *  - só se o browser suporta service worker;
 *  - só em contexto seguro (HTTPS ou localhost). Fora disso — ex.: E2E em http://laravel.test —
 *    o register rejeitaria; pulamos para não poluir o console;
 *  - só com build real (`app-asset` != 'dev'), espelhando o guard do `versionWatcher`, para não
 *    interferir no HMR do Vite em desenvolvimento.
 */
export function registerServiceWorker() {
    if (typeof navigator === 'undefined' || !('serviceWorker' in navigator)) {
        return;
    }
    if (!window.isSecureContext) {
        return;
    }

    const asset = document.querySelector('meta[name="app-asset"]')?.content;
    if (!asset || asset === 'dev') {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {
            /* registro é best-effort — uma falha não pode derrubar o app. */
        });
    });
}
