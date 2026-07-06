<?php

namespace App\Domain\Enriquecimento;

use Illuminate\Support\Facades\Log;

/**
 * Decorator de fallback (ADR-012): tenta a fonte primária; em falha
 * (transitória/estrutural), tenta a secundária. Se a secundária também falhar, a
 * exceção propaga — quem chama (o Job) cuida do retry/backoff.
 */
final class FallbackEnriquecedor implements EnriquecedorCnpj
{
    public function __construct(
        private readonly EnriquecedorCnpj $primaria,
        private readonly EnriquecedorCnpj $secundaria,
    ) {}

    public function consultar(string $cnpj): EmitenteEnriquecido
    {
        try {
            return $this->primaria->consultar($cnpj);
        } catch (EnriquecimentoException $e) {
            Log::info('Enriquecimento: fonte primária falhou, tentando fallback.', [
                'tipo' => $e->tipo,
                'erro' => $e->getMessage(),
            ]);

            return $this->secundaria->consultar($cnpj);
        }
    }
}
