import { useId } from 'react';

/**
 * ChoiceField — wrapper compartilhado dos controles de escolha do DS (STORY-005):
 * Checkbox, Radio e Switch. Layout de linha (controle + label inline) com alvo de
 * toque ≥48px (`min-h-3xl`), hint e mensagem de erro com wiring de a11y
 * (`aria-invalid`, `aria-describedby`, `role="alert"`).
 *
 * O controle é fornecido via render-prop, recebendo os props de a11y já montados e o
 * `labelId`. `labelMode="htmlFor"` (checkbox/radio nativos) usa `<label htmlFor>`;
 * `labelMode="labelledby"` (switch = `<button role="switch">`, não rotulável por
 * `<label>`) usa `aria-labelledby`. Selecionado é indicado por `primary` no controle.
 */
export default function ChoiceField({
    label,
    hint,
    error,
    fieldTestId,
    disabled = false,
    labelMode = 'htmlFor',
    children,
}) {
    const id = useId();
    const labelId = `${id}-label`;
    const hintId = `${id}-hint`;
    const errorId = `${id}-error`;
    const describedBy =
        [hint ? hintId : null, error ? errorId : null].filter(Boolean).join(' ') || undefined;

    const controlProps = {
        id,
        'aria-invalid': error ? 'true' : undefined,
        'aria-describedby': describedBy,
        disabled: disabled || undefined,
    };

    const RowTag = labelMode === 'htmlFor' ? 'label' : 'div';
    const rowProps = labelMode === 'htmlFor' ? { htmlFor: id } : {};

    return (
        <div className="flex flex-col gap-xs">
            <RowTag
                {...rowProps}
                data-testid={fieldTestId}
                className={`flex min-h-3xl items-center gap-md ${
                    disabled ? 'cursor-not-allowed opacity-[0.38]' : 'cursor-pointer'
                }`}
            >
                {children(controlProps, labelId)}
                <span
                    id={labelMode === 'labelledby' ? labelId : undefined}
                    className="text-body-md text-ink"
                >
                    {label}
                </span>
            </RowTag>

            {hint && (
                <span id={hintId} className="text-body-sm text-body">
                    {hint}
                </span>
            )}
            {error && (
                <span
                    id={errorId}
                    role="alert"
                    className="text-body-sm font-semibold text-negative-darkest"
                >
                    {error}
                </span>
            )}
        </div>
    );
}
