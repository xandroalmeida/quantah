<?php

namespace App\Domain\Coleta\Sefaz;

use App\Domain\Coleta\ChaveAcesso;

/**
 * Contrato do adaptador de extração por estado (ADR-001/002).
 *
 * Cada UF tem sua implementação (no MVP só SP). O adaptador é a ACL que isola o
 * HTML/portal da SEFAZ do modelo canônico: o núcleo nunca sabe que existe scraping.
 * Adicionar um estado — ou migrar para a fonte oficial — é uma nova implementação
 * desta interface, sem tocar o modelo canônico.
 */
interface SefazAdapter
{
    /** Código da UF que este adaptador atende (ex.: "35" para SP). */
    public function uf(): string;

    /**
     * Extrai e normaliza o cupom, retornando o DTO canônico já sem PII.
     *
     * @param  string|null  $qrConteudo  QR/URL original capturado (necessário para a
     *                                   consulta assinada ao portal — ver SefazSpFetcher).
     *
     * @throws SefazExtracaoException falha transitória, estrutural ou de negócio (ADR-002).
     */
    public function extrair(ChaveAcesso $chave, ?string $qrConteudo = null): CupomExtraido;
}
