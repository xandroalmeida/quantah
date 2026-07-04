<?php

namespace App\Domain\Saque;

use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Saque;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Operação do saque no backoffice (STORY-017, ADR-005) — a máquina de estados.
 *
 * solicitado → em_analise → aprovado → pago ; em_analise → rejeitado (estorno).
 * Cada transição valida o estado de origem (integridade + idempotência: rejeitar já
 * rejeitado é barrado, então não estorna duas vezes). O estorno devolve o valor ao saldo
 * (crédito no ledger `estorno_saque`), sob lock, na mesma transação da mudança de estado.
 */
final class SaqueService
{
    /** solicitado → em_analise: o operador assume o KYC. */
    public function assumir(Saque $saque, User $operador): Saque
    {
        $this->exigirEstado($saque, Saque::STATUS_SOLICITADO, 'assumir');

        $saque->update([
            'status' => Saque::STATUS_EM_ANALISE,
            'processado_por' => $operador->id,
        ]);

        return $saque;
    }

    /** em_analise → aprovado: KYC ok (CPF confere com a chave PIX). */
    public function aprovar(Saque $saque): Saque
    {
        $this->exigirEstado($saque, Saque::STATUS_EM_ANALISE, 'aprovar');

        $saque->update(['status' => Saque::STATUS_APROVADO]);

        return $saque;
    }

    /** aprovado → pago: PIX executado por fora, comprovante (e2e) anexado. */
    public function pagar(Saque $saque, string $comprovante): Saque
    {
        $this->exigirEstado($saque, Saque::STATUS_APROVADO, 'pagar');

        if (trim($comprovante) === '') {
            throw SaqueInvalidoException::comprovanteObrigatorio();
        }

        $saque->update([
            'status' => Saque::STATUS_PAGO,
            'comprovante' => trim($comprovante),
        ]);

        return $saque;
    }

    /** em_analise → rejeitado: KYC falha; **estorna** o valor ao saldo. */
    public function rejeitar(Saque $saque, ?string $motivo = null): Saque
    {
        $this->exigirEstado($saque, Saque::STATUS_EM_ANALISE, 'rejeitar');

        return DB::transaction(function () use ($saque, $motivo) {
            $carteira = Carteira::whereKey($saque->carteira_id)->lockForUpdate()->first();

            // Estorno: devolve o valor reservado ao ledger (positivo) e ao cache de saldo.
            CarteiraTransacao::create([
                'carteira_id' => $carteira->id,
                'tipo' => CarteiraTransacao::TIPO_ESTORNO_SAQUE,
                'valor_centavos' => $saque->valor_centavos,
                'saque_id' => $saque->id,
            ]);
            $carteira->increment('saldo_centavos', $saque->valor_centavos);

            $saque->update([
                'status' => Saque::STATUS_REJEITADO,
                'comprovante' => $motivo !== null && trim($motivo) !== '' ? trim($motivo) : $saque->comprovante,
            ]);

            return $saque;
        });
    }

    private function exigirEstado(Saque $saque, string $esperado, string $acao): void
    {
        if ($saque->status !== $esperado) {
            throw TransicaoInvalidaException::de($saque, $acao);
        }
    }
}
