<?php

namespace App\Domain\Saque;

use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Saque;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Solicitação de saque (STORY-017, ADR-005) — a **reserva** do valor.
 *
 * Garantias (núcleo de dinheiro):
 *  - **KYC mínimo**: CPF válido e **chave PIX do tipo CPF = CPF do titular** (titularidade
 *    por construção). Persiste o canônico sem máscara (ADR-006 / database-method.md).
 *  - **Valor mínimo** de R$ 5,00 (evita microtransferências operacionalmente caras).
 *  - **Saldo suficiente sob lock** (`FOR UPDATE`): impede saque duplo e saldo negativo
 *    (a invariante `saldo >= 0` também é CHECK no banco).
 *  - **Reserva atômica**: debita o ledger (`debito_saque`, negativo) e decrementa o saldo
 *    na MESMA transação que cria o `saque` em `solicitado` — saldo == SUM(ledger).
 */
final class SolicitarSaqueService
{
    /** Valor mínimo de saque no MVP — R$ 5,00 (decisão do dono, 2026-07-03). */
    public const VALOR_MINIMO_CENTAVOS = 500;

    public function solicitar(User $user, int $valorCentavos, string $cpf, string $chavePix): Saque
    {
        $cpf = Cpf::apenasDigitos($cpf);
        $chavePix = Cpf::apenasDigitos($chavePix);

        if ($valorCentavos < self::VALOR_MINIMO_CENTAVOS) {
            throw SaqueInvalidoException::valorAbaixoDoMinimo(self::VALOR_MINIMO_CENTAVOS);
        }
        if (! Cpf::ehValido($cpf)) {
            throw SaqueInvalidoException::cpfInvalido();
        }
        // Chave PIX do tipo CPF: precisa ser o CPF do titular (verificável sem serviço externo).
        if (! Cpf::ehValido($chavePix) || $chavePix !== $cpf) {
            throw SaqueInvalidoException::chaveNaoConfere();
        }

        return DB::transaction(function () use ($user, $valorCentavos, $cpf, $chavePix) {
            $carteira = Carteira::where('user_id', $user->id)->lockForUpdate()->first();

            if ($carteira === null || $carteira->saldo_centavos < $valorCentavos) {
                throw new SaldoInsuficienteException;
            }

            $saque = Saque::create([
                'carteira_id' => $carteira->id,
                'valor_centavos' => $valorCentavos,
                'cpf' => $cpf,
                'chave_pix' => $chavePix,
                'status' => Saque::STATUS_SOLICITADO,
            ]);

            // Reserva: debita o ledger (negativo) e o cache de saldo na mesma transação.
            CarteiraTransacao::create([
                'carteira_id' => $carteira->id,
                'tipo' => CarteiraTransacao::TIPO_DEBITO_SAQUE,
                'valor_centavos' => -$valorCentavos,
                'saque_id' => $saque->id,
            ]);
            $carteira->decrement('saldo_centavos', $valorCentavos);

            return $saque;
        });
    }
}
