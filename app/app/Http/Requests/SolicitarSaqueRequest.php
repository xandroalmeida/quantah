<?php

namespace App\Http\Requests;

use App\Domain\Saque\Cpf;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validação da solicitação de saque (STORY-017). Normaliza o `valor` (reais, tira máscara)
 * e o `cpf` (só dígitos, canônico — database-method.md) ANTES de validar. O mínimo (R$ 5,00)
 * e o CPF válido dão erro por campo; o saldo suficiente é checado no domínio sob lock.
 */
class SolicitarSaqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth garantida pelo middleware da rota
    }

    protected function prepareForValidation(): void
    {
        $valor = (string) $this->input('valor', '');
        $valor = str_replace(['R$', ' '], '', $valor);
        // pt-BR: "1.234,56" → remove milhar, vírgula vira ponto decimal.
        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        $this->merge([
            'valor' => $valor,
            'cpf' => Cpf::apenasDigitos((string) $this->input('cpf', '')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'valor' => ['required', 'numeric', 'min:5'], // reais; mínimo R$ 5,00
            'cpf' => ['required', 'string', function (string $attr, string $value, \Closure $fail) {
                if (! Cpf::ehValido($value)) {
                    $fail('CPF inválido.');
                }
            }],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'valor.required' => 'Informe o valor.',
            'valor.numeric' => 'Informe um valor válido.',
            'valor.min' => 'O valor mínimo de saque é R$ 5,00.',
        ];
    }
}
