/**
 * versionWatcher — auto-atualização do app quando o servidor sobe uma versão nova.
 *
 * Sem service worker (evita armadilhas de cache): o cliente guarda a assinatura do bundle
 * com que subiu (`bootAsset`) e consulta o endpoint `/version` em intervalo e — o caso
 * principal no PWA mobile — quando o app volta ao primeiro plano (visibilitychange/focus/
 * pageshow), pois o iOS "congela" a aba em segundo plano. Se a assinatura do servidor mudou
 * (deploy novo → novo bundle), recarrega para puxar os assets frescos (nomes com hash do
 * Vite). Não recarrega enquanto o usuário digita — adia até o próximo momento seguro, para
 * não perder o que ele preencheu. O Inertia já força reload na navegação quando os assets
 * mudam; isto cobre a atualização com o app ocioso/em segundo plano.
 */
const ENDPOINT = '/version';

/** Evita recarregar no meio de uma digitação (perderia o que o usuário preencheu). */
function digitando() {
    const el = document.activeElement;
    if (!el) {
        return false;
    }
    const tag = el.tagName;

    return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el.isContentEditable;
}

export function startVersionWatcher(bootAsset, { intervalMs = 60000 } = {}) {
    // Sem build (dev sem manifest) ou boot desconhecido → nada a vigiar.
    if (!bootAsset || bootAsset === 'dev') {
        return () => {};
    }

    let recarregando = false;
    let pendente = false;

    const recarregar = () => {
        if (recarregando) {
            return;
        }
        recarregando = true;
        window.location.reload();
    };

    const aplicarSePendente = () => {
        if (pendente && !digitando()) {
            recarregar();
        }
    };

    const checar = async () => {
        if (recarregando || document.hidden) {
            return;
        }
        if (pendente) {
            aplicarSePendente();

            return;
        }
        try {
            const res = await fetch(ENDPOINT, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (!res.ok) {
                return;
            }
            const data = await res.json();
            if (data && data.asset && data.asset !== bootAsset) {
                pendente = true;
                aplicarSePendente(); // recarrega já, se for seguro
            }
        } catch {
            // rede offline/instável — tenta de novo no próximo tick.
        }
    };

    // Voltar ao primeiro plano é o gatilho principal no mobile/PWA.
    const aoVoltar = () => {
        if (!document.hidden) {
            checar();
        }
    };

    document.addEventListener('visibilitychange', aoVoltar);
    window.addEventListener('focus', checar);
    window.addEventListener('pageshow', checar);
    // Ao sair de um campo, aplica um reload que ficou pendente por digitação.
    window.addEventListener('blur', aplicarSePendente, true);

    const timer = window.setInterval(checar, intervalMs);
    // Primeira checagem pouco depois do boot (não compete com o carregamento inicial).
    const inicial = window.setTimeout(checar, 3000);

    return () => {
        window.clearInterval(timer);
        window.clearTimeout(inicial);
        document.removeEventListener('visibilitychange', aoVoltar);
        window.removeEventListener('focus', checar);
        window.removeEventListener('pageshow', checar);
        window.removeEventListener('blur', aplicarSePendente, true);
    };
}
