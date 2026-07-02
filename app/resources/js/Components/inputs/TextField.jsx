import Field, { controlClasses } from './Field';

/**
 * TextField — `input.text` do DS (STORY-005). Label flutuante (spec), hint opcional,
 * estado de erro com mensagem acessível. Chrome e estados via tokens (Field).
 *
 * Props: `label`, `hint`, `error` (string → estado de erro), `hintTestId`/`errorTestId`
 * (para âncora de teste da vitrine), `className` e `...props` (value/onChange, type,
 * data-testid, etc.) repassados ao `<input>`.
 */
export default function TextField({
    label,
    hint,
    error,
    type = 'text',
    hintTestId,
    errorTestId,
    className = '',
    ...props
}) {
    return (
        <Field
            label={label}
            hint={hint}
            error={error}
            floatLabel
            disabled={props.disabled}
            hintTestId={hintTestId}
            errorTestId={errorTestId}
        >
            {(fieldProps) => (
                <input
                    type={type}
                    // `peer` + placeholder de espaço fazem o label flutuar por CSS puro.
                    placeholder=" "
                    className={controlClasses(!!error, `peer ${className}`)}
                    {...fieldProps}
                    {...props}
                />
            )}
        </Field>
    );
}
