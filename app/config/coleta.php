<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Janela de validade do cupom (STORY-035 · EPIC-007)
    |--------------------------------------------------------------------------
    |
    | Idade máxima, em dias, que um cupom pode ter (desde `data_emissao`) para
    | ser aceito na coleta. Cupom mais antigo que esta janela é rejeitado na
    | extração com o motivo `cupom_expirado` (não confundível com dedup/chave).
    |
    | Semântica (IDR-013): válido se a idade for ≤ N dias em America/Sao_Paulo
    | (limite inclusivo — exatamente N dias ainda vale); estritamente mais velho
    | é rejeitado. Ajustável sem deploy pela env COLETA_JANELA_DIAS.
    |
    */

    'janela_dias' => (int) env('COLETA_JANELA_DIAS', 7),

    /*
    |--------------------------------------------------------------------------
    | Janela de reprocessamento da extração (SEFAZ instável)
    |--------------------------------------------------------------------------
    |
    | Por quantas horas o ExtrairCupomJob reenfileira (com backoff) diante de
    | falha TRANSITÓRIA da SEFAZ-SP (timeout/5xx/429), antes de desistir e marcar
    | o cupom `falha`. A SEFAZ tem apagões de minutos/horas; o cupom fica "em
    | processamento" e valida sozinho quando o portal volta. Ajustável por env.
    |
    */

    'extracao_retry_horas' => (int) env('COLETA_EXTRACAO_RETRY_HORAS', 24),

];
