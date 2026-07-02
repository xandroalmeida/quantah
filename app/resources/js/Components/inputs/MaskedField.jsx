import { IMaskInput } from 'react-imask';
import Field, { controlClasses } from './Field';

/**
 * MaskedField — `input.masked` do DS (STORY-005). Variante de texto para formato
 * conhecido. A máscara é **ajuda de UX, não validação** (IDR-003): o valor persistido
 * é o **unmasked** (só dígitos) — o consumidor recebe esse valor canônico via
 * `onAccept(unmaskedValue)` e o guarda no `useForm`. A validação canônica é do servidor
 * (Laravel); nenhuma regra de negócio mora aqui.
 *
 * Máscara default: a **chave de acesso da NFC-e** (44 dígitos em 11 grupos de 4) — a
 * relevante à Onda 1. Placeholder mostra o formato; `inputMode="numeric"`.
 */
export const NFCE_KEY_MASK = '0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000';

export default function MaskedField({
    label,
    hint,
    error,
    mask = NFCE_KEY_MASK,
    value = '',
    onAccept,
    placeholder = NFCE_KEY_MASK,
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
                <IMaskInput
                    mask={mask}
                    // `unmask` guarda/expõe o valor sem máscara (regra de ouro da stack).
                    unmask
                    value={value}
                    onAccept={(_value, maskRef) => onAccept?.(maskRef.unmaskedValue)}
                    placeholder={placeholder}
                    inputMode="numeric"
                    className={controlClasses(!!error, className)}
                    {...fieldProps}
                    {...props}
                />
            )}
        </Field>
    );
}
