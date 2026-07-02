import ChoiceField from './ChoiceField';

/**
 * Radio — `input.radio` do DS (STORY-005). Radio nativo estilizado por token: borda
 * `ink`, indicador de selecionado `primary`, foco `ink`. Uma opção por instância; o
 * agrupamento (mesmo `name`) e o `<fieldset>`/legenda são montados pelo consumidor.
 */
export default function Radio({
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
                    type="radio"
                    className={`h-lg w-lg border-ink text-primary focus:ring-2 focus:ring-ink focus:ring-offset-0 ${className}`}
                    {...controlProps}
                    {...props}
                />
            )}
        </ChoiceField>
    );
}
