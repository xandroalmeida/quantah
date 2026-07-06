<?php

namespace App\Domain\Enriquecimento;

/**
 * DTO canônico do emitente enriquecido (ADR-012). É o único contrato que o domínio
 * conhece — nenhum consumidor toca o shape cru do provedor (BrasilAPI/Minha Receita).
 *
 * O `status` distingue os três desfechos de negócio de uma consulta bem-sucedida:
 *  - ENRIQUECIDO: veio com CNAE principal.
 *  - SEM_CNAE: a fonte respondeu, mas sem CNAE principal.
 *  - NAO_ENCONTRADO: CNPJ inexistente/baixado na fonte (404).
 * Falhas de transporte (timeout/5xx/429/contrato) NÃO viram DTO — viram
 * EnriquecimentoException (transitória/estrutural).
 */
final class EmitenteEnriquecido
{
    public const STATUS_ENRIQUECIDO = 'enriquecido';

    public const STATUS_SEM_CNAE = 'sem_cnae';

    public const STATUS_NAO_ENCONTRADO = 'nao_encontrado';

    /**
     * @param  array<int, array{codigo: string, descricao: ?string}>  $cnaesSecundarios
     */
    public function __construct(
        public readonly string $cnpj,
        public readonly ?string $razaoSocial,
        public readonly ?string $nomeFantasia,
        public readonly ?string $cnaePrincipalCodigo,
        public readonly ?string $cnaePrincipalDescricao,
        public readonly array $cnaesSecundarios,
        public readonly ?string $situacaoCadastral,
        public readonly ?string $municipio,
        public readonly ?string $uf,
        public readonly string $status,
        public readonly string $fonte,
    ) {}

    /**
     * @param  array<int, array{codigo: string, descricao: ?string}>  $cnaesSecundarios
     */
    public static function enriquecido(
        string $cnpj,
        ?string $razaoSocial,
        ?string $nomeFantasia,
        ?string $cnaePrincipalCodigo,
        ?string $cnaePrincipalDescricao,
        array $cnaesSecundarios,
        ?string $situacaoCadastral,
        ?string $municipio,
        ?string $uf,
        string $fonte,
    ): self {
        return new self(
            cnpj: $cnpj,
            razaoSocial: $razaoSocial,
            nomeFantasia: $nomeFantasia,
            cnaePrincipalCodigo: $cnaePrincipalCodigo,
            cnaePrincipalDescricao: $cnaePrincipalDescricao,
            cnaesSecundarios: $cnaesSecundarios,
            situacaoCadastral: $situacaoCadastral,
            municipio: $municipio,
            uf: $uf,
            status: $cnaePrincipalCodigo !== null && $cnaePrincipalCodigo !== ''
                ? self::STATUS_ENRIQUECIDO
                : self::STATUS_SEM_CNAE,
            fonte: $fonte,
        );
    }

    public static function naoEncontrado(string $cnpj, string $fonte): self
    {
        return new self(
            cnpj: $cnpj,
            razaoSocial: null,
            nomeFantasia: null,
            cnaePrincipalCodigo: null,
            cnaePrincipalDescricao: null,
            cnaesSecundarios: [],
            situacaoCadastral: null,
            municipio: null,
            uf: null,
            status: self::STATUS_NAO_ENCONTRADO,
            fonte: $fonte,
        );
    }
}
