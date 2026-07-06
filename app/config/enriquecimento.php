<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TTL do cache de CNPJ (STORY-040 · EPIC-009 · ADR-014)
    |--------------------------------------------------------------------------
    |
    | Idade máxima, em dias, de um registro de emitente antes de ser reconsultado
    | na fonte pública. Dentro do TTL, uma nova solicitação NÃO bate na API
    | externa (métrica de eficiência do épico). Default 30 dias, ajustável sem
    | deploy pela env ENRIQUECIMENTO_TTL_DIAS. A tela de edição chega no EPIC-012;
    | por ora é configuração semeada (PDR-004 regra 4).
    |
    */

    'ttl_dias' => (int) env('ENRIQUECIMENTO_TTL_DIAS', 30),

    /*
    |--------------------------------------------------------------------------
    | Cliente HTTP da consulta (ADR-012)
    |--------------------------------------------------------------------------
    */

    'timeout_segundos' => (int) env('ENRIQUECIMENTO_TIMEOUT', 15),

    'user_agent' => env('ENRIQUECIMENTO_UA', 'QuantahBot/1.0 (+enriquecimento CNPJ RFB)'),

];
