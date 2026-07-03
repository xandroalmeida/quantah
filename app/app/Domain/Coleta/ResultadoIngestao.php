<?php

namespace App\Domain\Coleta;

use App\Models\Cupom;

/**
 * Resultado da ingestão de um cupom (ADR-001/003) — o que a camada web recebe.
 * A web só conhece este DTO e o IngestaoCupomService, nunca o Eloquent do cupom.
 */
final class ResultadoIngestao
{
    public const CAPTURADO = 'capturado';      // recebido e persistido `pendente` (captura/handoff — STORY-009)

    public const ACEITO = 'aceito';           // novo cupom, extraído e validado

    public const DUPLICADO = 'duplicado';      // chave já existia (idempotente)

    public const REJEITADO = 'rejeitado';      // chave inválida / fora de escopo (não vira dado)

    public const FALHA_EXTRACAO = 'falha_extracao'; // aceito, mas extração falhou (reprocessável)

    private function __construct(
        public readonly string $situacao,
        public readonly ?Cupom $cupom,
        public readonly ?string $motivo,
    ) {}

    public static function capturado(Cupom $cupom): self
    {
        return new self(self::CAPTURADO, $cupom, null);
    }

    public static function aceito(Cupom $cupom): self
    {
        return new self(self::ACEITO, $cupom, null);
    }

    public static function duplicado(Cupom $cupom): self
    {
        return new self(self::DUPLICADO, $cupom, null);
    }

    public static function rejeitado(string $motivo, ?Cupom $cupom = null): self
    {
        return new self(self::REJEITADO, $cupom, $motivo);
    }

    public static function falhaExtracao(Cupom $cupom, string $motivo): self
    {
        return new self(self::FALHA_EXTRACAO, $cupom, $motivo);
    }

    public function foiRejeitado(): bool
    {
        return $this->situacao === self::REJEITADO;
    }

    /**
     * Forma serializável para flash/props do Inertia (STORY-009). Sem PII.
     *
     * @return array{situacao: string, chave: ?string, motivo: ?string}
     */
    public function toArray(): array
    {
        return [
            'situacao' => $this->situacao,
            'chave' => $this->cupom?->chave_acesso,
            'motivo' => $this->motivo,
        ];
    }
}
