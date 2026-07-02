import ChoiceField from './ChoiceField';

/**
 * Checkbox — `input.checkbox` do DS (STORY-005). Checkbox nativo (acessível por
 * teclado) estilizado por token: borda `ink`, indicador de selecionado `primary`
 * (via @tailwindcss/forms → `text-primary` pinta o marcado), foco `ink`. Layout,
 * alvo ≥48px e wiring de a11y vêm do ChoiceField.
 */
export default function Checkbox({
    label,
    hint,
    error,
    fieldTestId,
    className = '',
    ...props
}) {
    return (
        <ChoiceField
            label={label}
            hint={hint}
            error={error}
            fieldTestId={fieldTestId}
            disabled={props.disabled}
        >
            {(controlProps) => (
                <input
                    type="checkbox"
                    // 24px + raio sm (8px) = quadrado arredondado — distinto do radio (círculo).
                    className={`h-xl w-xl rounded-sm border-ink text-primary focus:ring-2 focus:ring-ink focus:ring-offset-0 ${className}`}
                    {...controlProps}
                    {...props}
                />
            )}
        </ChoiceField>
    );
}
