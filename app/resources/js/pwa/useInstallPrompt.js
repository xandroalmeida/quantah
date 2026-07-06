import { useCallback, useEffect, useState, useSyncExternalStore } from 'react';
import { instalar, podeInstalar, subscribe } from './installPrompt';

/**
 * useInstallPrompt — decide se e como oferecer a instalação da PWA na área do Coletador
 * (STORY-038 · EPIC-008).
 *
 * Retorna:
 *  - `mostrar`: exibir o convite? (não instalado, não dispensado, e há caminho de instalação);
 *  - `isIos`: no iOS mostramos instrução do "Adicionar à Tela de Início" (sem API de instalação);
 *  - `canInstall`: no Android/Chrome há um `beforeinstallprompt` capturado;
 *  - `promptInstall()`: dispara o prompt nativo (Android);
 *  - `dispensar()`: esconde e persiste a escolha (não insistir).
 */
const CHAVE_DISPENSADO = 'quantah:pwa-dismiss';

/** Já roda como app instalado (standalone)? Então não há o que oferecer. */
function rodandoInstalado() {
    if (typeof window === 'undefined') {
        return false;
    }
    const standalone = window.matchMedia?.('(display-mode: standalone)').matches;

    return Boolean(standalone) || window.navigator.standalone === true;
}

/** iOS (Safari) — não expõe `beforeinstallprompt`; instala só via A2HS manual. */
function ehIos() {
    if (typeof window === 'undefined') {
        return false;
    }
    const ua = window.navigator.userAgent || '';
    const iphone = /iphone|ipad|ipod/i.test(ua);
    // iPadOS recente se apresenta como "Macintosh" com tela sensível ao toque.
    const ipadDesktop =
        /Macintosh/.test(ua) && typeof document !== 'undefined' && 'ontouchend' in document;

    return iphone || ipadDesktop;
}

function lerDispensado() {
    try {
        return window.localStorage.getItem(CHAVE_DISPENSADO) === '1';
    } catch {
        return false;
    }
}

export function useInstallPrompt() {
    // Reage à chegada/uso do convite (store externo do módulo de captura).
    const canInstall = useSyncExternalStore(subscribe, podeInstalar, () => false);
    const [dispensado, setDispensado] = useState(lerDispensado);

    const instalado = rodandoInstalado();
    const isIos = ehIos();

    const dispensar = useCallback(() => {
        setDispensado(true);
        try {
            window.localStorage.setItem(CHAVE_DISPENSADO, '1');
        } catch {
            /* storage indisponível — só não persiste. */
        }
    }, []);

    const promptInstall = useCallback(async () => {
        const desfecho = await instalar();
        if (desfecho === 'accepted') {
            dispensar();
        }

        return desfecho;
    }, [dispensar]);

    // Ao rodar já instalado, some de vez.
    useEffect(() => {
        if (instalado) {
            setDispensado(true);
        }
    }, [instalado]);

    // Mostra o convite só se ainda não instalado, não dispensado, e há um caminho:
    // Android com convite pendente OU iOS (onde coach-amos o A2HS manual).
    const mostrar = !instalado && !dispensado && (canInstall || isIos);

    return { mostrar, isIos, canInstall, promptInstall, dispensar };
}
