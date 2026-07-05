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

];
