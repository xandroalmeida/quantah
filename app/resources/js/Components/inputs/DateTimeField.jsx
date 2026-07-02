import Field, { controlClasses } from './Field';

/**
 * DateTimeField — `input.datetime` do DS (STORY-005). Data/hora **por seletor**, nunca
 * digitação livre: usa o input nativo do browser (`type="date"` / `time` /
 * `datetime-local`), que já entrega calendário/relógio acessível e o valor canônico em
 * **ISO 8601** (`AAAA-MM-DD` / `HH:MM` / datetime). É o fallback simples que a sub-skill
 * inertia-react autoriza (IDR-003) — evita dep de calendário estilizado num componente
 * de DS sem tela de produto ainda.
 *
 * O consumidor recebe o valor ISO via `onChange(isoValue)` e o guarda no `useForm`. A
 * conversão de fuso e a validação canônica ficam no servidor.
 */
export default function DateTimeField({
    label,
    hint,
    error,
    type = 'date',
    value = '',
    onChange,
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
                <input
                    type={type}
                    value={value}
                    onChange={(event) => onChange?.(event.target.value)}
                    className={controlClasses(!!error, className)}
                    {...fieldProps}
                    {...props}
                />
            )}
        </Field>
    );
}
