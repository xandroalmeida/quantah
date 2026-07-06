/**
 * Captura e estado do convite de instalação da PWA (Android/Chrome) — STORY-038 · EPIC-008.
 *
 * O browser dispara `beforeinstallprompt` quando o app é instalável; o padrão é dar
 * `preventDefault()` (some com o mini-infobar) e GUARDAR o evento para disparar a instalação
 * sob um gesto do usuário — o `prompt()` só pode ser usado uma vez. Como o evento pode chegar
 * ANTES do React montar, a captura mora aqui e é iniciada no boot (app.jsx); os componentes
 * assinam via `subscribe` (store externo consumido por useSyncExternalStore).
 *
 * Nada aqui trata iOS: o Safari não expõe `beforeinstallprompt` (instala só via "Adicionar à
 * Tela de Início"). Esse ramo é decidido na UI a partir do user agent.
 */
let deferred = null;
const listeners = new Set();

function notificar() {
    for (const cb of listeners) {
        cb();
    }
}

/** Iniciado uma vez no boot (app.jsx), antes do primeiro render, para não perder o evento. */
export function initInstallCapture() {
    if (typeof window === 'undefined') {
        return;
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault(); // impede o mini-infobar padrão; instalamos sob gesto próprio.
        deferred = e;
        notificar();
    });

    // Instalou (por qualquer caminho) → não há mais o que oferecer.
    window.addEventListener('appinstalled', () => {
        deferred = null;
        notificar();
    });
}

/** Há um convite pendente para instalar (Android/Chrome)? */
export function podeInstalar() {
    return deferred !== null;
}

/**
 * Dispara o prompt nativo de instalação. Consome o convite (uso único) e resolve com o
 * desfecho: 'accepted' | 'dismissed' | null (indisponível/erro).
 */
export async function instalar() {
    if (!deferred) {
        return null;
    }
    const evt = deferred;
    deferred = null; // o prompt só vale uma vez.
    notificar();
    try {
        await evt.prompt();
        const escolha = await evt.userChoice;

        return escolha?.outcome ?? null;
    } catch {
        return null;
    }
}

/** Assina mudanças de estado (chegada/uso do convite). Retorna o unsubscribe. */
export function subscribe(cb) {
    listeners.add(cb);

    return () => listeners.delete(cb);
}
