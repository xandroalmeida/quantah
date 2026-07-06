/**
 * Service worker MÍNIMO do Quantah — habilita a instalabilidade da PWA (Chrome/Android
 * exige um service worker com fetch handler) SEM introduzir cache.
 *
 * Decisão deliberada (IDR-016): NÃO cacheamos nada aqui. O `versionWatcher`
 * (resources/js/versionWatcher.js) continua sendo o ÚNICO mecanismo de atualização — ele
 * recarrega o app quando o servidor sobe um deploy novo. Um SW que cacheasse assets
 * competiria com esse fluxo e reintroduziria as "armadilhas de cache" que evitamos de
 * propósito. Por isso: sem Cache Storage, sem precache, sem Workbox.
 *
 * O `fetch` handler existe mas é PASSIVO: como não chama `respondWith`, o browser trata cada
 * requisição normalmente (rede). Sua mera presença satisfaz o critério de instalabilidade; o
 * comportamento de rede fica idêntico ao de não ter service worker algum.
 */

self.addEventListener('install', () => {
    // Ativa a versão nova imediatamente, sem esperar as abas antigas fecharem.
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    // Assume o controle das abas abertas já nesta ativação (sem exigir reload).
    event.waitUntil(self.clients.claim());
});

// Handler presente porém passivo: sem respondWith → rede normal, sem cache.
self.addEventListener('fetch', () => {});
