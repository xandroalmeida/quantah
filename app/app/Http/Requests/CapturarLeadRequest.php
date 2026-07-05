<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validação da captação de lead B2B (STORY-026 · CA-3). Bloqueia campo ausente ou e-mail
 * inválido com mensagem por campo em pt-BR (não persiste). Pública (sem auth) — a landing B2B
 * não tem login. A deduplicação idempotente por e-mail é da ação de domínio `CapturarLead`
 * (não uma regra `unique` de validação, para não vazar a existência de terceiro — LGPD/CA-4).
 */
class CapturarLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190'],
            'empresa' => ['required', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome.',
            'empresa.required' => 'Informe a empresa.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Use um e-mail válido, com @ e domínio.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['nome' => 'nome', 'email' => 'e-mail', 'empresa' => 'empresa'];
    }
}
