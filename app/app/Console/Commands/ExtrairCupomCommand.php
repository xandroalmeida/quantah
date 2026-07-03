<?php

namespace App\Console\Commands;

use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\ResultadoIngestao;
use Illuminate\Console\Command;

/**
 * Exercita a ingestão síncrona ponta a ponta a partir de uma chave/URL (STORY-010).
 * Útil para verificar o pipeline em homologação com uma chave real de SP —
 * captura → extração (adaptador real) → dedup → persistência/classificação de falha.
 *
 *   php artisan coleta:extrair "35260112345678000195650010001234561000000019"
 */
class ExtrairCupomCommand extends Command
{
    protected $signature = 'coleta:extrair {chave : Chave de acesso (44 dígitos) ou URL do QR}';

    protected $description = 'Ingere e extrai um cupom de forma síncrona (verificação do pipeline)';

    public function handle(IngestaoCupomService $ingestao): int
    {
        $resultado = $ingestao->ingerir($this->argument('chave'), 'cli');

        $this->line("Situação: <info>{$resultado->situacao}</info>");
        if ($resultado->motivo) {
            $this->line("Motivo:   {$resultado->motivo}");
        }
        if ($resultado->cupom) {
            $this->line("Cupom:    {$resultado->cupom->chave_acesso} · status={$resultado->cupom->status}");
        }

        // Falha reprocessável (ex.: captcha) não é erro de execução do comando.
        return in_array($resultado->situacao, [
            ResultadoIngestao::ACEITO,
            ResultadoIngestao::CAPTURADO,
            ResultadoIngestao::DUPLICADO,
            ResultadoIngestao::FALHA_EXTRACAO,
        ], true) ? self::SUCCESS : self::FAILURE;
    }
}
