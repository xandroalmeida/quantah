import Field, { controlClasses } from './Field';

/**
 * SelectField — `input.select` do DS (STORY-005). Herda o chrome de input (tokens) e o
 * wiring de a11y do Field; label estático no topo. As `<option>` vêm como `children`.
 */
export default function SelectField({
    label,
    hint,
    error,
    children,
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
            disabled={props.disabled}
            hintTestId={hintTestId}
            errorTestId={errorTestId}
        >
            {(fieldProps) => (
                <select
                    className={controlClasses(!!error, className)}
                    {...fieldProps}
                    {...props}
                >
                    {children}
                </select>
            )}
        </Field>
    );
}
