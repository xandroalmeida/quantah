<?php

namespace App\Domain\Enriquecimento;

/**
 * Porta (ACL) da consulta cadastral de CNPJ (ADR-012). O domínio fala só com esta
 * interface; as implementações (BrasilAPI, Minha Receita, fallback) encapsulam o
 * externo e traduzem para o DTO canônico.
 */
interface EnriquecedorCnpj
{
    /**
     * Consulta os dados cadastrais do CNPJ (14 dígitos, sem máscara).
     *
     * @throws EnriquecimentoException em falha transitória (timeout/5xx/429) ou
     *                                 estrutural (contrato inesperado). CNPJ
     *                                 inexistente/sem CNAE volta como status no DTO.
     */
    public function consultar(string $cnpj): EmitenteEnriquecido;
}
