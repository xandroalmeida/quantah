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
                    className="inline-flex min-h-3xl items-center rounded-full focus:outline-none focus:ring-2 focus:ring-ink focus:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-[0.38]"
                    {...controlProps}
                    {...props}
                >
                    <span
                        className={`relative h-xl w-3xl rounded-full border border-ink transition-colors duration-fast ${
                            checked ? 'bg-primary' : 'bg-canvas-soft'
                        }`}
                    >
                        <span
                            className={`absolute top-xxs h-lg w-lg rounded-full bg-ink transition-transform duration-fast ${
                                checked ? 'translate-x-lg' : 'translate-x-xxs'
                            }`}
                        />
                    </span>
                </button>
            )}
        </ChoiceField>
    );
}
