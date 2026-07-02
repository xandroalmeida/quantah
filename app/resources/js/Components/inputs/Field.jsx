import { useId } from 'react';

/**
 * Field — wrapper compartilhado dos inputs de "caixa" do DS Quantah (STORY-005):
 * TextField, MaskedField, DateTimeField, SelectField. Encarna `input.text` da spec
 * (docs/project-state/design/system/components.md › Inputs): fundo `canvas`, texto
 * `ink`, borda 1px `ink` (elev.1), raio `md` (12px), padding `md lg`, `body-md`.
 *
 * Responsabilidades: label (flutuante em TextField, estático nos demais), hint,
 * mensagem de erro e o wiring de a11y — `aria-invalid`, `aria-describedby` ligando
 * hint + erro, `role="alert"` na mensagem (feedback nunca só por cor). O controle em
 * si é fornecido pelo componente concreto via render-prop, para o Field não precisar
 * conhecer input/select/IMask. Tudo por token — zero valor cru.
 */

// Chrome do controle. Único lugar (além do tailwind.config) onde as classes de
// token do input vivem — os concretos importam `controlClasses`.
export const CONTROL_BASE =
    'block w-full min-h-3xl rounded-md border bg-canvas px-lg py-md text-body-md text-ink ' +
    'placeholder:text-mute transition-colors duration-fast ' +
    'focus:outline-none focus:ring-2 focus:ring-offset-0 ' +
    'disabled:cursor-not-allowed disabled:opacity-[0.38]';

// Borda + foco por estado. Default = ink (elev.1); erro = negative (contraste UI ≥3:1).
const CONTROL_DEFAULT = 'border-ink focus:border-ink focus:ring-ink';
const CONTROL_ERROR = 'border-negative focus:border-negative focus:ring-negative';

/** Monta a className do controle conforme o estado de erro. */
export function controlClasses(error, className = '') {
    return `${CONTROL_BASE} ${error ? CONTROL_ERROR : CONTROL_DEFAULT} ${className}`.trim();
}

// Label flutuante do input.text: centrado quando vazio, sobe ao focar/preencher.
// Usa só variantes nativas do Tailwind (placeholder-shown + focus) — o input recebe
// `peer` e `placeholder=" "`. `bg-canvas px-xs` "corta" a borda quando flutuado.
const FLOAT_LABEL =
    'pointer-events-none absolute left-lg top-0 z-10 -translate-y-1/2 bg-canvas px-xs ' +
    'text-body-sm text-ink transition-all duration-fast ' +
    'peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-body-md peer-placeholder-shown:text-mute ' +
    'peer-focus:top-0 peer-focus:text-body-sm peer-focus:text-ink';

export default function Field({
    label,
    hint,
    error,
    floatLabel = false,
    disabled = false,
    hintTestId,
    errorTestId,
    children,
}) {
    const id = useId();
    const hintId = `${id}-hint`;
    const errorId = `${id}-error`;
    const describedBy =
        [hint ? hintId : null, error ? errorId : null].filter(Boolean).join(' ') || undefined;

    const control = children({
        id,
        'aria-invalid': error ? 'true' : undefined,
        'aria-describedby': describedBy,
        disabled: disabled || undefined,
    });

    return (
        <div className="flex flex-col gap-xs">
            {floatLabel ? (
                <div className="relative">
                    {control}
                    <label htmlFor={id} className={FLOAT_LABEL}>
                        {label}
                    </label>
                </div>
            ) : (
                <>
                    <label htmlFor={id} className="text-body-sm font-semibold text-ink">
                        {label}
                    </label>
                    {control}
                </>
            )}

            {hint && (
                <span id={hintId} data-testid={hintTestId} className="text-body-sm text-body">
                    {hint}
                </span>
            )}
            {error && (
                <span
                    id={errorId}
                    data-testid={errorTestId}
                    role="alert"
                    className="text-body-sm font-semibold text-negative-darkest"
                >
                    {error}
                </span>
            )}
        </div>
    );
}
