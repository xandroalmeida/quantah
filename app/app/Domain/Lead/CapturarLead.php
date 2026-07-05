<?php

namespace App\Domain\Lead;

use App\Models\Lead;

/**
 * CapturarLead — regra de captação/persistência do lead B2B (STORY-026, núcleo de negócio).
 *
 * Idempotente por e-mail normalizado (minúsculas + trim): reenviar o mesmo e-mail **não** cria
 * um segundo lead nem sobrescreve o existente (LGPD/CA-4 — a resposta não revela se o contato já
 * existia; quem chama devolve a mesma confirmação nos dois casos). Nome e empresa são apenas
 * aparados. Não registra PII em log.
 */
class CapturarLead
{
    /**
     * @param  array{nome: string, email: string, empresa: string}  $dados
     */
    public function __invoke(array $dados): Lead
    {
        $email = mb_strtolower(trim($dados['email']));

        // firstOrCreate: se o e-mail já existe, devolve o lead atual sem criar/sobrescrever.
        return Lead::firstOrCreate(
            ['email' => $email],
            ['nome' => trim($dados['nome']), 'empresa' => trim($dados['empresa'])],
        );
    }
}
