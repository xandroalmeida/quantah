<?php

namespace App\Domain\Coleta\Sefaz;

use App\Domain\Coleta\ChaveAcesso;

/**
 * Porta de saída para o portal público da SEFAZ-SP (ADR-002).
 *
 * Isola o acesso HTTP/scraping para que o adaptador seja testável sem rede: no
 * spike da STORY-008 uma implementação fake simula portal OK / instável / captcha.
 * A implementação real (scraping resiliente com throttle) é entrega da STORY-010.
 */
interface SefazSpFetcher
{
    /**
     * Retorna o payload bruto do portal (associativo) para a chave dada.
     * PODE conter CPF do consumidor — que será descartado pelo AnonimizadorCpf.
     *
     * @param  string|null  $qrConteudo  QR/URL original capturado (o `p` assinado que o
     *                                   portal exige). Sem ele, a consulta ao vivo de SP
     *                                   não é possível (só a chave não basta).
     * @return array<string, mixed>
     *
     * @throws SefazExtracaoException quando o portal falha (transitória/estrutural/negócio).
     */
    public function buscar(ChaveAcesso $chave, ?string $qrConteudo = null): array;
}
