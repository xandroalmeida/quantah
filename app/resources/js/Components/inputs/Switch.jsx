import ChoiceField from './ChoiceField';

/**
 * Switch — `input.switch` do DS (STORY-005). Não há elemento nativo de switch, então é
 * um `<button role="switch">` com `aria-checked` (acessível por teclado; Espaço/Enter
 * alternam nativamente no button). Ligado = trilho `primary` (indicador de selecionado);
 * desligado = `canvas-soft` com borda `ink`. Alvo de toque ≥48px (`min-h-3xl`).
 *
 * Controlado: `checked` + `onChange(next)`. O ChoiceField provê label (via
 * `aria-labelledby`, pois button não é rotulável por `<label htmlFor>`), hint e erro.
 */
export default function Switch({
    label,
    hint,
    error,
    checked = false,
    onChange,
    disabled = false,
    fieldTestId,
    ...props
}) {
    return (
        <ChoiceField
            label={label}
            hint={hint}
            error={error}
            fieldTestId={fieldTestId}
            disabled={disabled}
            labelMode="labelledby"
        >
            {(controlProps, labelId) => (
                <button
                    type="button"
                    role="switch"
                    aria-checked={checked}
                    aria-labelledby={labelId}
                    onClick={() => !disabled && onChange?.(!checked)}
                    // Botão = alvo de toque ≥48px (min-h-3xl). O anel de foco NÃO fica aqui
                    // (seria um círculo grande em volta da caixa) — vai no trilho via group-focus.
                    className="group inline-flex min-h-3xl items-center focus:outline-none disabled:cursor-not-allowed disabled:opacity-[0.38]"
                    {...controlProps}
                    {...props}
                >
                    {/* Flex mantém o thumb dentro do padding do trilho — sem overflow.
                        justify-start/end move o thumb; items-center centra na vertical.
                        Anel de foco em pílula, colado no trilho (group-focus). */}
                    <span
                        className={`flex h-xl w-3xl items-center rounded-full border border-ink px-xxs transition-colors duration-fast group-focus:ring-2 group-focus:ring-ink group-focus:ring-offset-2 group-focus:ring-offset-canvas ${
                            checked ? 'justify-end bg-primary' : 'justify-start bg-canvas-soft'
                        }`}
                    >
                        <span className="h-lg w-lg rounded-full bg-ink" />
                    </span>
                </button>
            )}
        </ChoiceField>
    );
}
