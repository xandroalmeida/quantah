/**
 * Fundação de i18n do front-end (STORY-020, ADR-011).
 *
 * A fonte única das strings é o `lang/` do Laravel; o dicionário do locale ativo chega ao
 * cliente pela prop compartilhada `translations` (HandleInertiaRequests) e é registrado aqui
 * uma vez no boot. O produto é monolíngue (pt-BR), então o mapa é idêntico entre páginas —
 * registrar no boot basta e mantém `t()` utilizável em qualquer lugar (inclusive fora de
 * componentes), sem framework de i18n de runtime.
 *
 * Chave = string-fonte em inglês (padrão de JSON translations do Laravel): `t('Log in')`.
 * Se a chave não existir no dicionário, devolve a própria chave — nunca quebra a tela.
 */
let dictionary = {};

/** Registra o dicionário do locale ativo (chamado no boot, a partir da prop `translations`). */
export function setTranslations(map) {
    dictionary = map || {};
}

/**
 * Traduz `key` para o locale ativo. `replacements` substitui placeholders `:nome`.
 * Ex.: t('Attempt :n of :total', { n: 2, total: 3 }).
 */
export function t(key, replacements = {}) {
    let text = Object.prototype.hasOwnProperty.call(dictionary, key)
        ? dictionary[key]
        : key;

    for (const [name, value] of Object.entries(replacements)) {
        text = text.replaceAll(`:${name}`, String(value));
    }

    return text;
}
