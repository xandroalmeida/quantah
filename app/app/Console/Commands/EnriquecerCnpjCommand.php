<?php

namespace App\Console\Commands;

use App\Domain\Enriquecimento\EnriquecimentoException;
use App\Domain\Enriquecimento\EnriquecimentoService;
use Illuminate\Console\Command;

/**
 * Exercita o enriquecimento de CNPJ de forma síncrona (STORY-040). Útil para verificar
 * a fonte pública em homologação com um CNPJ real de emitente — consulta → cache-first
 * → persistência na tabela `emitentes`.
 *
 *   php artisan enriquecimento:cnpj 43259548002883
 */
class EnriquecerCnpjCommand extends Command
{
    protected $signature = 'enriquecimento:cnpj {cnpj : CNPJ do emitente (14 dígitos, com ou sem máscara)}';

    protected $description = 'Enriquece um CNPJ de forma síncrona (verificação da fonte pública)';

    public function handle(EnriquecimentoService $service): int
    {
        try {
            $emitente = $service->enriquecer($this->argument('cnpj'));
        } catch (EnriquecimentoException $e) {
            $this->error("Falha {$e->tipo}: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->line("CNPJ:     <info>{$emitente->cnpj}</info>");
        $this->line("Razão:    {$emitente->razao_social}");
        $this->line("CNAE:     {$emitente->cnae_principal_codigo} · {$emitente->cnae_principal_descricao}");
        $this->line("Situação: {$emitente->situacao_cadastral} · {$emitente->municipio}/{$emitente->uf}");
        $this->line("Status:   {$emitente->status_enriquecimento} · fonte={$emitente->fonte}");

        return self::SUCCESS;
    }
}
