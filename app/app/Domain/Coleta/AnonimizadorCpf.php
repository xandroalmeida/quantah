<?php

namespace App\Domain\Coleta;

/**
 * Anonimização de CPF na coleta (ADR-006).
 *
 * O CPF não agrega valor ao produto (o valor é o preço do item, não quem comprou),
 * então a decisão é DESCARTAR — a forma mais forte de anonimizar: o dado que não
 * existe não vaza, não precisa de retenção nem de resposta a pedido de eliminação.
 *
 * `limpar()` remove recursivamente qualquer chave de CPF do payload e apaga
 * qualquer valor com cara de CPF (11 dígitos, formatado ou não), ANTES de qualquer
 * persistência ou log. Nenhum caminho do modelo canônico grava CPF em claro.
 */
final class AnonimizadorCpf
{
    /** Chaves cujo valor é (ou pode conter) CPF do consumidor. */
    private const CHAVES_CPF = ['cpf', 'cpf_consumidor', 'cpf_destinatario', 'consumidor_cpf', 'documento'];

    /** CPF formatado (000.000.000-00) ou 11 dígitos isolados. */
    private const PADRAO_CPF = '/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/';

    /** Marcador que substitui qualquer trecho com cara de CPF — nunca o valor original. */
    private const MARCADOR = '[CPF-REMOVIDO]';

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function limpar(array $payload): array
    {
        $limpo = [];

        foreach ($payload as $chave => $valor) {
            if (is_string($chave) && in_array(strtolower($chave), self::CHAVES_CPF, true)) {
                continue; // descarta a chave de CPF inteira
            }

            if (is_array($valor)) {
                $limpo[$chave] = $this->limpar($valor);
            } elseif (is_string($valor)) {
                $limpo[$chave] = $this->escovar($valor);
            } else {
                $limpo[$chave] = $valor;
            }
        }

        return $limpo;
    }

    /** Substitui qualquer trecho com cara de CPF por um marcador — nunca o valor original. */
    private function escovar(string $texto): string
    {
        return self::limparTexto($texto);
    }

    /**
     * Escova um texto avulso (mesma regra do payload). Usado no ponto de ingestão para
     * o conteúdo do QR colado pelo usuário (`qr_conteudo`), que pode trazer CPF grudado
     * como lixo de colagem e é persistido em tabela canônica (ADR-006, STORY-011 CA-1).
     */
    public static function limparTexto(string $texto): string
    {
        return preg_replace(self::PADRAO_CPF, self::MARCADOR, $texto) ?? $texto;
    }

    /** Utilitário de regressão: há CPF neste texto? (usado por testes anti-vazamento) */
    public static function contemCpf(string $texto): bool
    {
        return preg_match(self::PADRAO_CPF, $texto) === 1;
    }
}
