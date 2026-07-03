<?php

namespace App\Domain\Coleta;

/**
 * Value object da chave de acesso da NFC-e (44 dígitos).
 *
 * Spike da STORY-008 — prova o caminho das ADR-001/003: a chave é a identidade
 * natural do cupom e carrega informação estruturada (UF, ano/mês, CNPJ, modelo)
 * que permite validar e deduplicar ANTES de acessar o portal (visao §6.1/§6.4).
 *
 * Valida formato (44 dígitos) e dígito verificador (mod 11). O escopo de negócio
 * (UF = SP, modelo = NFC-e) é decidido pelo IngestaoCupomService, não aqui.
 */
final class ChaveAcesso
{
    private function __construct(private readonly string $valor) {}

    /**
     * Constrói a partir de uma chave crua ("35...") ou de uma URL de QR da SEFAZ
     * (`...?p=CHAVE|2|1|...`). Lança se não for uma chave de 44 dígitos com DV válido.
     *
     * @throws ChaveAcessoInvalidaException
     */
    public static function deEntrada(string $entrada): self
    {
        $entrada = trim($entrada);

        // Se veio a URL do QR, a chave é o parâmetro `p` (antes do primeiro `|`).
        if (preg_match('/[?&]p=([^&]+)/i', $entrada, $m)) {
            $candidato = explode('|', $m[1])[0];
        } else {
            $candidato = $entrada;
        }

        $digitos = preg_replace('/\D/', '', $candidato) ?? '';

        if (strlen($digitos) !== 44) {
            throw new ChaveAcessoInvalidaException(
                'Chave de acesso deve ter exatamente 44 dígitos numéricos.'
            );
        }

        if (self::digitoVerificador(substr($digitos, 0, 43)) !== (int) $digitos[43]) {
            throw new ChaveAcessoInvalidaException(
                'Dígito verificador (mod 11) da chave de acesso não confere.'
            );
        }

        return new self($digitos);
    }

    /**
     * Dígito verificador da chave (mod 11) sobre os 43 primeiros dígitos.
     * Pesos 2..9 ciclando da direita para a esquerda.
     */
    public static function digitoVerificador(string $prefixo43): int
    {
        $pesos = [2, 3, 4, 5, 6, 7, 8, 9];
        $soma = 0;
        $invertido = strrev($prefixo43);

        for ($i = 0, $n = strlen($invertido); $i < $n; $i++) {
            $soma += (int) $invertido[$i] * $pesos[$i % 8];
        }

        $resto = $soma % 11;

        return $resto <= 1 ? 0 : 11 - $resto;
    }

    public function valor(): string
    {
        return $this->valor;
    }

    /** Código da UF emitente (cUF). SP = 35. */
    public function uf(): string
    {
        return substr($this->valor, 0, 2);
    }

    /** Ano/mês de emissão (AAMM), derivado da chave. */
    public function anoMes(): string
    {
        return substr($this->valor, 2, 4);
    }

    /** CNPJ do emitente (empresa, não é dado do consumidor). */
    public function cnpjEmitente(): string
    {
        return substr($this->valor, 6, 14);
    }

    /** Modelo do documento fiscal. NFC-e = 65. */
    public function modelo(): string
    {
        return substr($this->valor, 20, 2);
    }

    public function serie(): int
    {
        return (int) substr($this->valor, 22, 3);
    }

    public function numero(): int
    {
        return (int) substr($this->valor, 25, 9);
    }

    public function __toString(): string
    {
        return $this->valor;
    }
}
