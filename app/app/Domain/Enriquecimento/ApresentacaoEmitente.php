<?php

namespace App\Domain\Enriquecimento;

use App\Models\Emitente;
use App\Support\Formato;

/**
 * Read-model do emitente para o Backoffice (STORY-041 · EPIC-009).
 *
 * Traduz o registro de enriquecimento (ou a ausência dele) num view-model claro para o
 * operador: cada estado tem rótulo legível e variante de badge — nunca um campo vazio
 * mudo (CA-4). CNAE, situação e localização só aparecem quando existem.
 */
final class ApresentacaoEmitente
{
    /**
     * @return array{estado: string, estado_rotulo: string, badge_variante: string, cnpj: string, razao_social: ?string, nome_fantasia: ?string, cnae: ?string, situacao_cadastral: ?string, localizacao: ?string}
     */
    public static function montar(?Emitente $emitente, ?string $cnpj): array
    {
        $cnpjFmt = Formato::cnpj($cnpj);

        if ($emitente === null) {
            return self::vazio('pendente', 'Enriquecimento pendente', 'warning', $cnpjFmt);
        }

        return match ($emitente->status_enriquecimento) {
            Emitente::STATUS_ENRIQUECIDO => self::comDados('enriquecido', 'Enriquecido', 'positive', $cnpjFmt, $emitente),
            Emitente::STATUS_SEM_CNAE => self::comDados('sem_cnae', 'Sem CNAE informado', 'warning', $cnpjFmt, $emitente),
            Emitente::STATUS_NAO_ENCONTRADO => self::vazio('nao_encontrado', 'CNPJ não encontrado na Receita', 'negative', $cnpjFmt),
            default => self::vazio('indisponivel', 'Enriquecimento indisponível', 'negative', $cnpjFmt),
        };
    }

    private static function comDados(string $estado, string $rotulo, string $variante, string $cnpj, Emitente $e): array
    {
        return [
            'estado' => $estado,
            'estado_rotulo' => $rotulo,
            'badge_variante' => $variante,
            'cnpj' => $cnpj,
            'razao_social' => self::vazioParaNulo($e->razao_social),
            'nome_fantasia' => self::vazioParaNulo($e->nome_fantasia),
            'cnae' => self::cnae($e->cnae_principal_codigo, $e->cnae_principal_descricao),
            'situacao_cadastral' => self::vazioParaNulo($e->situacao_cadastral),
            'localizacao' => self::localizacao($e->municipio, $e->uf),
        ];
    }

    private static function vazio(string $estado, string $rotulo, string $variante, string $cnpj): array
    {
        return [
            'estado' => $estado,
            'estado_rotulo' => $rotulo,
            'badge_variante' => $variante,
            'cnpj' => $cnpj,
            'razao_social' => null,
            'nome_fantasia' => null,
            'cnae' => null,
            'situacao_cadastral' => null,
            'localizacao' => null,
        ];
    }

    /** Formata o CNAE de 7 dígitos como `XXXX-X/XX — descrição`. */
    private static function cnae(?string $codigo, ?string $descricao): ?string
    {
        if ($codigo === null || ! preg_match('/^\d{7}$/', $codigo)) {
            return null;
        }

        $formatado = substr($codigo, 0, 4).'-'.substr($codigo, 4, 1).'/'.substr($codigo, 5, 2);

        return ($descricao !== null && $descricao !== '')
            ? $formatado.' — '.$descricao
            : $formatado;
    }

    private static function localizacao(?string $municipio, ?string $uf): ?string
    {
        if ($municipio === null || $municipio === '' || $uf === null || $uf === '') {
            return null;
        }

        return $municipio.'/'.$uf;
    }

    private static function vazioParaNulo(?string $valor): ?string
    {
        return ($valor !== null && $valor !== '') ? $valor : null;
    }
}
