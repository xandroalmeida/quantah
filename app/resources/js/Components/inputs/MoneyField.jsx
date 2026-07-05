import { IMaskInput } from 'react-imask';
import Field, { controlClasses } from './Field';

/**
 * MoneyField — variante monetária pt-BR do `input.masked` do DS (STORY-005). Milhar `.`,
 * decimal `,`, 2 casas (ex.: `1.234,56`). Como o `MaskedField`, a máscara é ajuda de UX,
 * não validação (IDR-003): o valor guardado é a **string formatada** e o servidor
 * (`SolicitarSaqueRequest::prepareForValidation`) normaliza pt-BR → decimal antes de validar,
 * então tanto `20` quanto `1.234,56` chegam corretos. Digitar só dígitos → reais inteiros
 * (`20` = R$ 20,00), consistente com o contrato dos E2E.
 *
 * Props: `label`, `hint`, `error`, `value` (string formatada), `onAccept(value)` (guarda no
 * useForm), `placeholder` (default `0,00`), `className` e `...props` repassados ao input.
 */
// Opções do Number mask do IMask. No react-imask elas vão como props IRMÃS de `mask`
// (spread), não aninhadas — aninhar faz o IMask tratar como máscara de padrão e não formatar.
export const MONEY_MASK = {
    mask: Number,
    scale: 2,
    thousandsSeparator: '.',
    radix: ',',
    // Cola/teclado numérico com ponto vira vírgula decimal (não milhar).
    mapToRadix: ['.'],
    normalizeZeros: true,
    padFractionalZeros: false,
    min: 0,
};

export default function MoneyField({
    label,
    hint,
    error,
    value = '',
    onAccept,
    placeholder = '0,00',
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
                    {...MONEY_MASK}
                    value={String(value ?? '')}
                    // 1º arg = valor mascarado (string formatada) — é o que persistimos.
                    onAccept={(masked) => onAccept?.(masked)}
                    placeholder={placeholder}
                    inputMode="decimal"
                    className={controlClasses(!!error, className)}
                    {...fieldProps}
                    {...props}
                />
            )}
        </Field>
    );
}
